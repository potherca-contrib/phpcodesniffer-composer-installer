<?php

namespace Potherca\PhpUnit
{
    class GetCompatibleExceptionName
    {
        //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
        /**
         * @param string $exceptionName
         *
         * @return string
         *
         * @throws \PHPUnit_Framework_AssertionFailedError|\PHPUnit\Framework\AssertionFailedError
         * @throws \PHPUnit_Framework_SkippedTestError|\PHPUnit\Framework\SkippedTestError
         */
        final public function __invoke($exceptionName)
        {
            $matchingExceptionName = '';

            $exceptionName = ltrim($exceptionName, '\\');

            if ($this->isPhpUnitExceptionNeeded($exceptionName) === false) {
                if ($exceptionName === 'DivisionByZeroError') {
                    $this->expectExceptionMessage('Division by zero');
                    $matchingExceptionName = '\PHPUnit_Framework_Error_Warning';
                } else {
                    $matchingExceptionName = '\\'.$exceptionName;
                }
            } else {
                if ($exceptionName === 'ParseError') {
                    $this->markTestSkipped('Parse errors can not be caught in PHP5');
                } else {
                    $matchingExceptionName = $this->getMatchingExceptionName($exceptionName);
                }
            }

            return $matchingExceptionName;
        }

        ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

        /**
         * @param string $exceptionName
         *
         * @return bool
         */
        private function isPhpUnitExceptionNeeded($exceptionName)
        {
            return class_exists('\\' . $exceptionName) === false
                /* @NOTE: The line below validates that the Exception does not extend the PHP7 "Throwable" interface */
                || class_implements('\\' . $exceptionName) === [];
        }

        /**
         * @param $exceptionName
         *
         * @return string
         */
        private function getMatchingExceptionName($exceptionName)
        {
            $matchingExceptions = [
                'ArgumentCountError' => '\PHPUnit_Framework_Error',
                'AssertionError' => '\PHPUnit_Framework_Error_Warning',
                'DivisionByZeroError' => '\PHPUnit_Framework_Error_Warning',
                'Error' => '\PHPUnit_Framework_Error',
                'TypeError' => '\PHPUnit_Framework_Error',
            ];

            if (array_key_exists($exceptionName, $matchingExceptions) === false) {
                $errorMessage = vsprintf('Could not find an exception for class name "%s"', [$exceptionName]);
                $this->fail($errorMessage);
            }

            return $matchingExceptions[$exceptionName];
        }
    }

    class SetNonPublicProperty
    {
        //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

        /**
         * Sets a given value for a given (private or protected) property on a given object
         *
         * @param object $subject
         * @param string $name
         * @param mixed $value
         */
        final public function __invoke($subject, $name, $value)
        {
            $reflectionObject = new \ReflectionObject($subject);

            $properties = $this->getProperties($reflectionObject);

            array_walk($properties, function (\ReflectionProperty $reflectionProperty) use ($subject, $name, $value) {
                if ($reflectionProperty->getName() === $name) {

                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($subject, $value);
                    // @CHECKME: This could spell trouble for protected properties
                    $reflectionProperty->setAccessible(false);
                }
            });
        }

        ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

        /**
         * @param $reflectionObject
         *
         * @return array
         */
        private function getProperties(\ReflectionObject $reflectionObject)
        {
            $properties = $reflectionObject->getProperties();

            $reflectionClass = $reflectionObject;

            while ($parent = $reflectionClass->getParentClass()) {
                $properties = array_merge($properties, $parent->getProperties());
                $reflectionClass = $parent;
            }

            return $properties;
        }
    }

    function callShim($caller)
    {
        $class =  __NAMESPACE__.'\\'.$name;

        $map = [
            'expectException' => 'setExpectedException',
        ];

        return function ($name, $parameters) use ($caller, $class, $map) {

            if (class_exists($class)) {
                $trait = new $name();
                return call_user_func_array([$trait, '__invoke'], $parameters);
            } elseif (array_key_exists($name, $map)) {
                return call_user_func_array([$caller, $map[$name]], $parameters);
            } else {
                // ?
            }
        };
    }

    if (class_exists('\PHPUnit\Framework\TestCase')) {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /** @noinspection PhpUndefinedClassInspection */
        abstract class AbstractTestCase extends \PHPUnit\Framework\TestCase
        {
            use \Potherca\PhpUnit\CreateDataProviderTrait;
            use \Potherca\PhpUnit\CreateObjectFromAbstractClassTrait;
            use \Potherca\PhpUnit\GetCompatibleExceptionNameTrait;
            use \Potherca\PhpUnit\SetNonPublicPropertyTrait;

            final public function __call($name, $parameters)
            {
                $shim = callShim($this);
                return $shim($name, $parameters);
            }
        }
    } elseif (class_exists('\PHPUnit_Framework_TestCase')) {
        /** @noinspection PhpUndefinedClassInspection */
        abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
        {
            final public function __call($name, $parameters)
            {
                $shim = callShim($this);
                return $shim($name, $parameters);
            }
        }
    } else {
        $message = vsprintf(
            'Could not run tests. Could not find either "%s" or "%s" class.', [
                '\PHPUnit_Framework_TestCase',
                '\PHPUnit\Framework\TestCase',
            ]
        );

        throw new \RuntimeException($message);
    }
}

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer {
    abstract class AbstractTestCase extends \Potherca\PhpUnit\AbstractTestCase {}
}

/*EOF*/
