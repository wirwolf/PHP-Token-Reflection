<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use TokenReflection\Exception;

/**
 * Docblock parser.
 */
class ReflectionAnnotation
{
	/**
	 * Main description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const SHORT_DESCRIPTION = ' short_description';

	/**
	 * Sub description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const LONG_DESCRIPTION = ' long_description';

	/**
	 * List of applied templates.
	 *
	 * @var array
	 */
	private $templates = array();

	/**
	 * Parsed annotations.
	 *
	 * @var array
	 */
	private $annotations;

	/**
	 * Element docblock.
	 *
	 * False if none.
	 *
	 * @var string|false
	 */
	private $docComment;

	/**
	 * Constructor.
	 *
	 * @param string|false $docComment Docblock definition
	 */
	public function __construct($docComment = null)
	{
		$this->docComment = $docComment ?: false;
	}

	/**
	 * Returns the docblock.
	 *
	 * @return string|false
	 */
	public function getDocComment()
	{
		return $this->docComment;
	}

	/**
	 * Returns if the current docblock contains the requrested annotation.
	 *
	 * @param string $annotation Annotation name
	 * @return boolean
	 */
	public function hasAnnotation($annotation)
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return isset($this->annotations[$annotation]);
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $annotation Annotation name
	 * @return string|array|null
	 */
	public function getAnnotation($annotation)
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return isset($this->annotations[$annotation]) ? $this->annotations[$annotation] : null;
	}

	/**
	 * Returns all parsed annotations.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return $this->annotations;
	}

	/**
	 * Sets Docblock templates.
	 *
	 * @param array $templates Docblock templates
	 * @return \TokenReflection\ReflectionAnnotation
	 * @throws \TokenReflection\Exception\Runtime If an invalid annotation template was provided
	 */
	public function setTemplates(array $templates)
	{
		foreach ($templates as $template) {
			if (!$template instanceof ReflectionAnnotation) {
				throw new Exception\Runtime(
					sprintf(
						'All templates have to be instances of \\TokenReflection\\ReflectionAnnotation; %s given.',
						is_object($template) ? get_class($template) : gettype($template)
					),
					Exception\Runtime::INVALID_ARGUMENT
				);
			}
		}

		$this->templates = $templates;
	}

	/**
	 * Parses reflection object documentation.
	 */
	private function parse()
	{
		$this->annotations = array();

		if (false !== $this->docComment) {
			// Parse docblock
			$name = self::SHORT_DESCRIPTION;
			$docblock = trim(preg_replace(
				array(
					'~^' . preg_quote(ReflectionBase::DOCBLOCK_TEMPLATE_START, '~') . '~',
					'~^' . preg_quote(ReflectionBase::DOCBLOCK_TEMPLATE_END, '~') . '$~',
					'~^/\\*\\*~',
					'~\\*/$~'
				),
				'',
				$this->docComment
			));
			foreach (explode("\n", $docblock) as $line) {
				$line = preg_replace('~^\\*\\s*~', '', trim($line));

				// End of short description
				if ('' === $line && self::SHORT_DESCRIPTION === $name) {
					$name = self::LONG_DESCRIPTION;
					continue;
				}

				// @annotation
				if (preg_match('~^@([\\S]+)\\s*(.*)~', $line, $matches)) {
					$name = $matches[1];
					$this->annotations[$name][] = $matches[2];
					continue;
				}

				// Continuation
				if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
					if (!isset($this->annotations[$name])) {
						$this->annotations[$name] = $line;
					} else {
						$this->annotations[$name] .= "\n" . $line;
					}
				} else {
					$this->annotations[$name][count($this->annotations[$name]) - 1] .= "\n" . $line;
				}
			}

			array_walk_recursive($this->annotations, function(&$value) {
				// {@*} is a placeholder for */ (phpDocumentor compatibility)
				$value = str_replace('{@*}', '*/', $value);
				$value = trim($value);
			});
		}

		$this->mergeTemplates();
	}

	/**
	 * Merges templates with the current docblock.
	 */
	private function mergeTemplates()
	{
		foreach ($this->templates as $index => $template) {
			if (0 === $index && $template->getDocComment() === $this->docComment) {
				continue;
			}

			foreach ($template->getAnnotations() as $name => $value) {
				if ($name === self::LONG_DESCRIPTION) {
					// Long description
					if (isset($this->annotations[self::LONG_DESCRIPTION])) {
						$this->annotations[self::LONG_DESCRIPTION] = $value . "\n" . $this->annotations[self::LONG_DESCRIPTION];
					} else {
						$this->annotations[self::LONG_DESCRIPTION] = $value;
					}
				} elseif ($name !== self::SHORT_DESCRIPTION) {
					// Tags; short description is not inherited
					if (isset($this->annotations[$name])) {
						$this->annotations[$name] = array_merge($this->annotations[$name], $value);
					} else {
						$this->annotations[$name] = $value;
					}
				}
			}
		}
	}
}