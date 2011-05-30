<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionClassTest extends Test
{
	protected $type = 'class';

	public function testConstants()
	{
		$rfl = $this->getClassReflection('constants');

		$this->assertSame($rfl->internal->hasConstant('STRING'), $rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasConstant('STRING'));
		$this->assertTrue($rfl->token->hasOwnConstant('STRING'));
		$this->assertSame($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('PARENT'));

		$this->assertSame($rfl->internal->getConstant('STRING'), $rfl->token->getConstant('STRING'));
		$this->assertSame('string', $rfl->token->getConstant('STRING'));
		$this->assertSame($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertSame($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertSame(array('STRING' => 'string', 'INTEGER' => 1, 'FLOAT' => 1.1, 'BOOLEAN' => true, 'PARENT' => 'parent'), $rfl->token->getConstants());
		$this->assertSame(array('STRING' => 'string', 'INTEGER' => 1, 'FLOAT' => 1.1, 'BOOLEAN' => true), $rfl->token->getOwnConstants());
		$this->assertSame(range(0, 3), array_keys($rfl->token->getOwnConstantReflections()));
		foreach ($rfl->token->getOwnConstantReflections() as $constant) {
			$this->assertInstanceOf('TokenReflection\ReflectionConstant', $constant);
		}

		$rfl = $this->getClassReflection('noConstants');

		$this->assertSame($rfl->internal->hasConstant('NONEXISTENT'), $rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->hasOwnConstant('NONEXISTENT'));

		$this->assertSame($rfl->internal->getConstant('NONEXISTENT'), $rfl->token->getConstant('NONEXISTENT'));
		$this->assertFalse($rfl->token->getConstant('NONEXISTENT'));
		$this->assertSame($rfl->internal->getConstants(), $rfl->token->getConstants());
		$this->assertSame(array(), $rfl->token->getConstants());
		$this->assertSame(array(), $rfl->token->getOwnConstants());
		$this->assertSame(array(), $rfl->token->getOwnConstantReflections());
	}

	public function testProperties()
	{
		ReflectionProperty::setParseValueDefinitions(true);
		$rfl = $this->getClassReflection('properties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)));
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array('publicStatic' => true, 'privateStatic' => 'something', 'protectedStatic' => 1, 'public' => false, 'private' => '', 'protected' => 0), $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array('publicStatic' => true, 'privateStatic' => 'something', 'protectedStatic' => 1), $rfl->token->getStaticProperties());

		$properties = array('public', 'publicStatic', 'protectedStatic', 'protectedStatic', 'private', 'privateStatic');
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('TokenReflection\ReflectionProperty', $rfl->token->getProperty($property));
		}

		$properties = array('public', 'publicStatic', 'private', 'privateStatic');
		foreach ($properties as $property) {
			$this->assertTrue($rfl->token->hasOwnProperty($property));
		}
		$properties = array('protectedStatic', 'protectedStatic');
		foreach ($properties as $property) {
			$this->assertFalse($rfl->token->hasOwnProperty($property));
		}

		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertTrue($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->getStaticPropertyValue('protectedStatic');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('privateStatic');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertSame($rfl->internal->setStaticPropertyValue('publicStatic', false), $rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertNull($rfl->token->setStaticPropertyValue('publicStatic', false));
		$this->assertSame($rfl->internal->getStaticPropertyValue('publicStatic'), $rfl->token->getStaticPropertyValue('publicStatic'));
		$this->assertFalse($rfl->token->getStaticPropertyValue('publicStatic'));

		try {
			$rfl->token->setStaticPropertyValue('protectedStatic', 0);
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('privateStatic', '');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('noProperties');

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array(), $rfl->token->getDefaultProperties());
		$this->assertSame($rfl->internal->getProperties(), $rfl->token->getProperties());
		$this->assertSame(array(), $rfl->token->getProperties());
		$this->assertSame(array(), $rfl->token->getOwnProperties());
		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array(), $rfl->token->getStaticProperties());

		$this->assertSame($rfl->internal->hasProperty('nonExistent'), $rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasProperty('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnProperty('nonExistent'));

		try {
			$rfl->token->getProperty('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->getStaticPropertyValue('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		try {
			$rfl->token->setStaticPropertyValue('property', 'property');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('doubleProperties');

		$filters = array(\ReflectionProperty::IS_STATIC, \ReflectionProperty::IS_PUBLIC, \ReflectionProperty::IS_PROTECTED, \ReflectionProperty::IS_PRIVATE);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getProperties($filter)), array_keys($rfl->token->getProperties($filter)), $filter);
			foreach ($rfl->token->getProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
			foreach ($rfl->token->getOwnProperties($filter) as $property) {
				$this->assertInstanceOf('TokenReflection\ReflectionProperty', $property);
			}
		}

		$this->assertSame($rfl->internal->getDefaultProperties(), $rfl->token->getDefaultProperties());
		$this->assertSame(array('protectedOne' => 1, 'protectedTwo' => 0, 'publicOne' => true, 'publicTwo' => false, 'privateOne' => 'something', 'privateTwo' => ''), $rfl->token->getDefaultProperties());

		$this->assertSame($rfl->internal->getStaticProperties(), $rfl->token->getStaticProperties());
		$this->assertSame(array('protectedOne' => 1, 'protectedTwo' => 0), $rfl->token->getStaticProperties());

		$properties = array('publicOne', 'publicTwo', 'protectedOne', 'protectedTwo', 'privateOne', 'privateTwo');
		foreach ($properties as $property) {
			$this->assertSame($rfl->internal->hasProperty($property), $rfl->token->hasProperty($property));
			$this->assertTrue($rfl->token->hasProperty($property));

			$this->assertInstanceOf('TokenReflection\ReflectionProperty', $rfl->token->getProperty($property));
		}

		ReflectionProperty::setParseValueDefinitions(false);
	}

	public function testInstantiableCloneable()
	{
		$rfl = $this->getClassReflection('publicConstructor');
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
// Not yet in the internal reflection
//		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateConstructor');
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
// Not yet in the internal reflection
//		 $this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertFalse($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('publicClone');
// Not yet in the internal reflection
//		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertTrue($rfl->token->isCloneable());

		$rfl = $this->getClassReflection('privateClone');
// Not yet in the internal reflection
//		$this->assertSame($rfl->internal->isCloneable(), $rfl->token->isCloneable());
		$this->assertFalse($rfl->token->isCloneable());
	}

	public function testMethods()
	{
		$rfl = $this->getClassReflection('methods');

		$filters = array(\ReflectionMethod::IS_STATIC, \ReflectionMethod::IS_PUBLIC, \ReflectionMethod::IS_PROTECTED, \ReflectionMethod::IS_PRIVATE, \ReflectionMethod::IS_ABSTRACT, \ReflectionMethod::IS_FINAL);
		foreach ($this->getFilterCombinations($filters) as $filter) {
			$this->assertSame(array_keys($rfl->internal->getMethods($filter)), array_keys($rfl->token->getMethods($filter)));
			foreach ($rfl->token->getMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
			foreach ($rfl->token->getOwnMethods($filter) as $method) {
				$this->assertInstanceOf('TokenReflection\ReflectionMethod', $method);
			}
		}

		$methods = array('__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'protectedStaticFunction', 'privateStaticFunction', 'publicFunction', 'protectedFunction', 'privateFunction');
		foreach ($methods as $method) {
			$this->assertSame($rfl->internal->hasMethod($method), $rfl->token->hasMethod($method));
			$this->assertTrue($rfl->token->hasMethod($method));

			$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getMethod($method));
		}

		$methods = array('__construct', '__destruct', 'publicFinalFunction', 'publicStaticFunction', 'privateStaticFunction', 'publicFunction', 'privateFunction');
		foreach ($methods as $method) {
			$this->assertTrue($rfl->token->hasOwnMethod($method));
		}
		$methods = array('protectedStaticFunction', 'protectedFunction');
		foreach ($methods as $method) {
			$this->assertFalse($rfl->token->hasOwnMethod($method));
		}

		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getConstructor());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $rfl->token->getDestructor());

		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$rfl = $this->getClassReflection('noMethods');

		$this->assertSame($rfl->internal->getMethods(), $rfl->token->getMethods());
		$this->assertSame(array(), $rfl->token->getMethods());
		$this->assertSame(array(), $rfl->token->getOwnMethods());

		try {
			$rfl->token->getMethod('nonExistent');
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		$this->assertSame($rfl->internal->hasMethod('nonExistent'), $rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasMethod('nonExistent'));
		$this->assertFalse($rfl->token->hasOwnMethod('nonExistent'));

		$this->assertSame($rfl->internal->getConstructor(), $rfl->token->getConstructor());
		$this->assertNull($rfl->token->getConstructor());
		$this->assertNull($rfl->token->getDestructor());
	}

	public function testLines()
	{
		$rfl = $this->getClassReflection('lines');
		$this->assertSame($rfl->internal->getStartLine(), $rfl->token->getStartLine());
		$this->assertSame(3, $rfl->token->getStartLine());
		$this->assertSame($rfl->internal->getEndLine(), $rfl->token->getEndLine());
		$this->assertSame(5, $rfl->token->getEndLine());
	}

	public function testInstances()
	{
		$rfl = $this->getClassReflection('instances');

		$this->assertSame($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstances(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstances(1)));
		$this->assertSame($rfl->internal->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)), $rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertTrue($rfl->token->isInstance(new \TokenReflection_Test_ClassInstancesChild(1)));
		$this->assertSame($rfl->internal->isInstance(new \Exception()), $rfl->token->isInstance(new \Exception()));
		$this->assertFalse($rfl->token->isInstance(new \Exception()));

		$this->assertEquals($rfl->internal->newInstance(1), $rfl->token->newInstance(1));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstance(1));
		$this->assertEquals($rfl->internal->newInstanceArgs(array(1)), $rfl->token->newInstanceArgs(array(1)));
		$this->assertInstanceOf($this->getClassName('instances'), $rfl->token->newInstanceArgs(array(1)));
	}

	public function testAbstract()
	{
		$rfl = $this->getClassReflection('abstract');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(\ReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('abstractImplicit');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertTrue($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(\ReflectionClass::IS_IMPLICIT_ABSTRACT | \ReflectionClass::IS_EXPLICIT_ABSTRACT, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noAbstract');
		$this->assertSame($rfl->internal->isAbstract(), $rfl->token->isAbstract());
		$this->assertFalse($rfl->token->isAbstract());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(0, $rfl->token->getModifiers());
	}

	public function testFinal()
	{
		$rfl = $this->getClassReflection('final');
		$this->assertSame($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertTrue($rfl->token->isFinal());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(\ReflectionClass::IS_FINAL, $rfl->token->getModifiers());

		$rfl = $this->getClassReflection('noFinal');
		$this->assertSame($rfl->internal->isFinal(), $rfl->token->isFinal());
		$this->assertFalse($rfl->token->isFinal());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame(0, $rfl->token->getModifiers());
	}

	public function testInterface()
	{
		$rfl = $this->getClassReflection('interface');
		$this->assertSame($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertTrue($rfl->token->isInterface());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertFalse($rfl->token->isInstantiable());

		$rfl = $this->getClassReflection('noInterface');
		$this->assertSame($rfl->internal->isInterface(), $rfl->token->isInterface());
		$this->assertFalse($rfl->token->isInterface());
		$this->assertSame($rfl->internal->isInstantiable(), $rfl->token->isInstantiable());
		$this->assertTrue($rfl->token->isInstantiable());
	}

	public function testInterfaces()
	{
		$rfl = $this->getClassReflection('interfaces');

		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame(array('Traversable', 'Iterator', 'Countable'), $rfl->token->getInterfaceNames());
		$this->assertSame(array('Countable'), $rfl->token->getOwnInterfaceNames());
		$this->assertSame(array_keys($rfl->internal->getInterfaces()), array_keys($rfl->token->getInterfaces()));
		$this->assertSame(array('Traversable', 'Iterator', 'Countable'), array_keys($rfl->token->getInterfaces()));
		$this->assertSame(array('Countable'), array_keys($rfl->token->getOwnInterfaces()));
		foreach ($rfl->token->getInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		foreach ($rfl->token->getOwnInterfaces() as $interface) {
			$this->assertInstanceOf('TokenReflection\Php\ReflectionClass', $interface);
		}
		$this->assertSame($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface('Countable'));
		$this->assertTrue($rfl->token->implementsInterface(new \ReflectionClass('Countable')));

		$rfl = $this->getClassReflection('noInterfaces');
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
		$this->assertSame($rfl->internal->getInterfaceNames(), $rfl->token->getInterfaceNames());
		$this->assertSame(array(), $rfl->token->getOwnInterfaceNames());
		$this->assertSame(array(), $rfl->token->getInterfaceNames());
		$this->assertSame($rfl->internal->getInterfaces(), $rfl->token->getInterfaces());
		$this->assertSame(array(), $rfl->token->getInterfaces());
		$this->assertSame(array(), $rfl->token->getOwnInterfaces());
		$this->assertSame($rfl->internal->implementsInterface('Countable'), $rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface('Countable'));
		$this->assertFalse($rfl->token->implementsInterface(new \ReflectionClass('Countable')));
	}

	public function testIterator()
	{
		$rfl = $this->getClassReflection('iterator');
		$this->assertSame($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertTrue($rfl->token->isIterateable());

		$rfl = $this->getClassReflection('noIterator');
		$this->assertSame($rfl->internal->isIterateable(), $rfl->token->isIterateable());
		$this->assertFalse($rfl->token->isIterateable());
	}

	public function testParent()
	{
		$rfl = $this->getClassReflection('parent');
		foreach (array('TokenReflection_Test_ClassGrandGrandParent', 'TokenReflection_Test_ClassGrandParent') as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($parent));
			$this->assertTrue($rfl->token->isSubclassOf($this->getBroker()->getClass($parent)));
		}
		foreach (array('TokenReflection_Test_ClassParent', 'Exception', 'DateTime') as $parent) {
			$this->assertSame($rfl->internal->isSubclassOf($parent), $rfl->token->isSubclassOf($parent));
			$this->assertFalse($rfl->token->isSubclassOf($parent));
		}
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $rfl->token->getParentClass());
		$this->assertSame('TokenReflection_Test_ClassGrandParent', $rfl->token->getParentClassName());

		$this->assertSame(2, count($rfl->token->getParentClasses()));
		foreach ($rfl->token->getParentClasses() as $class) {
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $class);
		}
		$this->assertSame(array('TokenReflection_Test_ClassGrandParent', 'TokenReflection_Test_ClassGrandGrandParent'), $rfl->token->getParentClassNameList());

		$rfl = $this->getClassReflection('noParent');
		$this->assertSame($rfl->internal->isSubclassOf('Exception'), $rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf('Exception'));
		$this->assertFalse($rfl->token->isSubclassOf(new \ReflectionClass('Exception')));

		$this->assertSame($rfl->internal->getParentClass(), $rfl->token->getParentClass());
		$this->assertFalse($rfl->token->getParentClass());
		$this->assertSame(array(), $rfl->token->getParentClasses());
		$this->assertSame(array(), $rfl->token->getParentClassNameList());
	}

	public function testUserDefined()
	{
		$rfl = $this->getClassReflection('userDefined');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertTrue($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertSame($this->getFilePath('userDefined'), $rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertFalse($rfl->token->isInternal());

		$this->assertSame($rfl->internal->getExtension(), $rfl->token->getExtension());
		$this->assertNull($rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertFalse($rfl->token->getExtensionName());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('Exception');
		$rfl->token = $this->getBroker()->getClass('Exception');

		$this->assertSame($rfl->internal->isUserDefined(), $rfl->token->isUserDefined());
		$this->assertFalse($rfl->token->isUserDefined());
		$this->assertSame($rfl->internal->getFileName(), $rfl->token->getFileName());
		$this->assertFalse($rfl->token->getFileName());
		$this->assertSame($rfl->internal->isInternal(), $rfl->token->isInternal());
		$this->assertTrue($rfl->token->isInternal());

		$this->assertInstanceOf('TokenReflection\Php\ReflectionExtension', $rfl->token->getExtension());
		$this->assertSame($rfl->internal->getExtensionName(), $rfl->token->getExtensionName());
		$this->assertSame('Core', $rfl->token->getExtensionName());
	}

	public function testDocComment()
	{
		$rfl = $this->getClassReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */", $rfl->token->getDocComment());

		$rfl = $this->getClassReflection('noDocComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	public function testDocCommentInheritance()
	{
		require_once $this->getFilePath('docCommentInheritance');
		$this->getBroker()->processFile($this->getFilePath('docCommentInheritance'));

		$parent = new \stdClass();
		$parent->internal = new \ReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceParent');
		$this->assertSame($parent->internal->getDocComment(), $parent->token->getDocComment());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceExplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame('My Short description.', $rfl->token->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Long description. Phew, that was long.', $rfl->token->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_ClassDocCommentInheritanceImplicit');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame($parent->token->getAnnotations(), $rfl->token->getAnnotations());
	}

	public function testInNamespace()
	{
		require_once $this->getFilePath('inNamespace');
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionClass('TokenReflection\Test\ClassInNamespace');
		$rfl->token = $this->getBroker()->getClass('TokenReflection\Test\ClassInNamespace');

		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertTrue($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('TokenReflection\Test', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame('TokenReflection\Test\ClassInNamespace', $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame('ClassInNamespace', $rfl->token->getShortName());

		$rfl = $this->getClassReflection('noNamespace');
		$this->assertSame($rfl->internal->inNamespace(), $rfl->token->inNamespace());
		$this->assertFalse($rfl->token->inNamespace());
		$this->assertSame($rfl->internal->getNamespaceName(), $rfl->token->getNamespaceName());
		$this->assertSame('', $rfl->token->getNamespaceName());
		$this->assertSame($rfl->internal->getName(), $rfl->token->getName());
		$this->assertSame($this->getClassName('noNamespace'), $rfl->token->getName());
		$this->assertSame($rfl->internal->getShortName(), $rfl->token->getShortName());
		$this->assertSame($this->getClassName('noNamespace'), $rfl->token->getShortName());
	}

	public function testPropertyGetSource()
	{
		static $expected = array(
			'publicStatic' => 'public static $publicStatic = true;',
			'privateStatic' => 'private static $privateStatic = \'something\';',
			'protectedStatic' => 'protected static $protectedStatic = 1;',
			'public' => 'public $public = false;',
			'protected' => 'protected $protected = 0;',
			'private' => 'private $private = \'\';'
		);

		$rfl = $this->getClassReflection('properties')->token;
		foreach ($expected as $propertyName => $source) {
			$this->assertSame($source, $rfl->getProperty($propertyName)->getSource());
		}
	}

	public function testMethodGetSource()
	{
		static $expected = array(
			'protectedStaticFunction' => "protected static function protectedStaticFunction()\n	{\n	}",
			'protectedFunction' => "protected function protectedFunction()\n	{\n	}",
			'publicStaticFunction' => "public static function publicStaticFunction()\n	{\n	}"
		);

		$rfl = $this->getClassReflection('methods')->token;
		foreach ($expected as $methodName => $source) {
			$this->assertSame($source, $rfl->getMethod($methodName)->getSource());
		}
	}

	public function testConstantGetSource()
	{
		static $expected = array(
			'PARENT' => 'PARENT = \'parent\';',
			'STRING' => 'STRING = \'string\';',
			'FLOAT' => 'FLOAT = 1.1;',
			'BOOLEAN' => 'BOOLEAN = true;'
		);

		$rfl = $this->getClassReflection('constants')->token;
		foreach ($expected as $constantName => $source) {
			$this->assertSame($source, $rfl->getConstantReflection($constantName)->getSource());
		}
	}

	public function testClassGetSource()
	{
		static $expected = array(
			'methods' => "class TokenReflection_Test_ClassMethods extends TokenReflection_Test_ClassMethodsParent\n{\n	public function __construct()\n	{\n	}\n\n	public function __destruct()\n	{\n	}\n\n	public final function publicFinalFunction()\n	{\n	}\n\n	public static function publicStaticFunction()\n	{\n	}\n\n	private static function privateStaticFunction()\n	{\n	}\n\n	public function publicFunction()\n	{\n	}\n\n	private function privateFunction()\n	{\n	}\n}",
			'constants' => "class TokenReflection_Test_ClassConstants extends TokenReflection_Test_ClassConstantsParent\n{\n	const STRING = 'string';\n	const INTEGER = 1;\n	const FLOAT = 1.1;\n	const BOOLEAN = true;\n}",
			'docComment' => "/**\n * TokenReflection_Test_ClassDocComment.\n *\n * @copyright Copyright (c) 2011\n * @author author\n * @see http://php.net\n */\nclass TokenReflection_Test_ClassDocComment\n{\n}"
		);

		foreach ($expected as $className => $source) {
			$this->assertSame(
				$source,
				$this->getClassReflection($className)->token->getSource()
			);
		}
	}
}
