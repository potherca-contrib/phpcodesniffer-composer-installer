<?php

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer;

use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;

/**
 * @coversDefaultClass \Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Plugin
 * @covers ::<!public>
 */
class PluginTest  extends AbstractTestCase
{
    ////////////////////////////////// FIXTURES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const MOCK_DIR = 'mock-directory';

    /** @var Plugin */
    private $plugin;

    final public function setup()
    {
        $this->plugin = new Plugin();
    }

    /**
     * Assert that a given method exists for a given class
     *
     * @param string $class name of the class
     * @param string $method name of the method
     *
     * @throws ReflectionException if $class don't exist
     * @throws PHPUnit_Framework_ExpectationFailedException if a method isn't found
     */
    final public static function assertMethodExist($class, $method)
    {
        $oReflectionClass = new \ReflectionClass($class);

        $message = vsprintf('Class "%s" does not have a method "%s"', array($class, $method));

        $methodExists = $oReflectionClass->hasMethod($method);

        self::assertTrue($methodExists, $message);
    }

    /////////////////////////////////// TESTS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @covers ::__construct
     */
    final public function testPluginShouldNotReceiveParametersWhenInstantiated()
    {
        $actual = new Plugin();
        $expected = '\\Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin';

        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    final public function testPluginShouldReturnArrayWhenSubscringToEvents()
    {
        $actual = Plugin::getSubscribedEvents();
        $expected = 'array';

        self::assertInternalType($expected, $actual);

        return $actual;
    }

    /**
     * @coversNothing
     *
     * @depends testPluginShouldReturnArrayWhenSubscringToEvents
     */
    final public function testPluginShouldSubscribeToPostInstallCommandWhenSubscringToEvents(array $actual)
    {
        self::assertArrayHasKey(ScriptEvents::POST_INSTALL_CMD, $actual);
    }

    /**
     * @coversNothing
     *
     * @depends testPluginShouldReturnArrayWhenSubscringToEvents
     */
    final public function testPluginShouldSubscribeToPostUpdateCommandWhenSubscringToEvents(array $actual)
    {
        self::assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $actual);
    }

    /**
     * @coversNothing
     *
     * @depends testPluginShouldReturnArrayWhenSubscringToEvents
     */
    final public function testPluginShouldProvideExistingMethodsWhenSubscringToEvents(array $actual)
    {
        array_walk($actual, function($event) {
            $method = $event[0][0];

            self::assertMethodExist(
                '\\Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin',
                $method
            );
        });
    }

    /**
     * @covers ::activate
     */
    final public function testPluginShouldReceiveComposerWhenActivated()
    {
        $plugin = $this->plugin;

        $exceptionName = $this->getCompatibleExceptionName('\\TypeError');

        $this->expectException($exceptionName);

        $this->setExpectedExceptionRegExp(
            $exceptionName,
            $this->regexMustBeAnInstanceOf('activate', 'Composer\\Composer')
        );

        /** @noinspection PhpParamsInspection */
        $plugin->activate();
    }

    /**
     * @covers ::activate
     */
    final public function testPluginShouldReceiveIoInterfaceWhenActivated()
    {
        $plugin = $this->plugin;

        $exceptionName = $this->getCompatibleExceptionName('\\TypeError');

        $this->expectException($exceptionName);

        $this->setExpectedExceptionRegExp(
            $exceptionName,
            $this->regexMustBeAnInstanceOf('activate', 'Composer\\IO\\IOInterface')
        );

        /** @noinspection PhpParamsInspection */
        $plugin->activate($this->getMockComposer());
    }

    /**
     * @covers ::activate
     */
    final public function testPluginShouldNotAskPhpCodeSnifferForInstalledPathsWhenPhpCodeSnifferNotIsInstalled()
    {
        $plugin = $this->plugin;

        $installedPackages = array();

        $mockComposer = $this->getMockComposer();
        $mockIo = $this->getMockIO();
        $mockProcessBuilder = $this->getMockProcessBuilder(count($installedPackages));

        $this->attachConfigToComposer($mockComposer);
        $this->attachRepositoryManagerToComposer($mockComposer, $installedPackages);

        $this->setNonPublicProperty($plugin, 'processBuilder', $mockProcessBuilder);

        $plugin->activate($mockComposer, $mockIo);
    }

    /**
     * @covers ::activate
     */
    final public function testPluginShouldAskPhpCodeSnifferForInstalledPathsWhenPhpCodeSnifferIsInstalled()
    {
        $plugin = $this->plugin;

        $installedPackages = array(Plugin::PACKAGE_NAME);

        $mockComposer = $this->getMockComposer();
        $mockIo = $this->getMockIO();
        $mockProcessBuilder = $this->getMockProcessBuilder(count($installedPackages));

        $this->attachConfigToComposer($mockComposer);
        $this->attachRepositoryManagerToComposer($mockComposer, $installedPackages);

        $this->setNonPublicProperty($plugin, 'processBuilder', $mockProcessBuilder);

        $plugin->activate($mockComposer, $mockIo);
    }

    /**
     * @runInSeparateProcess
     */
    final public function testPluginShouldAlreadyHaveIoWhenEventHandlerIsCalled()
    {
        $plugin = $this->plugin;

        if (interface_exists('\\Throwable') === false) {
            $this->markTestSkipped('Fatal errors can not be caught in PHP5');
        } else {
            $exceptionName = '\\Error';

            $this->expectException($exceptionName);

            $plugin->onDependenciesChangedEvent();
        }
    }

    /**
     * @runInSeparateProcess
     */
    final public function testPluginShouldAlreadyHaveComposerWhenEventHandlerIsCalled()
    {
        if (interface_exists('\\Throwable') === false) {
            $this->markTestSkipped('Fatal errors can not be caught in PHP5');
        } else {
            $plugin = $this->plugin;

            $this->expectException('\\Error');
            $this->expectExceptionMessage('Call to a member function getRepositoryManager() on null');

            $mockIo = $this->getMockIO();
            $this->setNonPublicProperty($plugin, 'io', $mockIo);

            $plugin->onDependenciesChangedEvent();
        }
    }

    final public function testPluginShouldCheckVerboseModeWhenEventHandlerIsCalled()
    {
        $plugin = $this->plugin;

        $installedPackages = [];

        $mockComposer = $this->getMockComposer();
        $mockIo = $this->getMockIO();
        $mockProcessBuilder = $this->getMockProcessBuilder(count($installedPackages), false);

        $this->attachRepositoryManagerToComposer($mockComposer, $installedPackages);

        $this->setNonPublicProperty($plugin, 'io', $mockIo);
        $this->setNonPublicProperty($plugin, 'composer', $mockComposer);

        $mockIo->expects($this->exactly(1))
            ->method('isVerbose')
        ;

        $plugin->onDependenciesChangedEvent();
    }

    final public function testPluginShould_WhenEventHandlerIsCalled()
    {
        $plugin = $this->plugin;

        $installedPackages = [];

        $mockComposer = $this->getMockComposer();
        $mockIo = $this->getMockIO();
        $mockProcessBuilder = $this->getMockProcessBuilder(count($installedPackages), false);

        $this->attachRepositoryManagerToComposer($mockComposer, $installedPackages);

        $this->setNonPublicProperty($plugin, 'io', $mockIo);
        $this->setNonPublicProperty($plugin, 'composer', $mockComposer);

        $mockIo->expects($this->exactly(1))
            ->method('isVerbose')
            ->willReturn(false)
        ;

        $plugin->onDependenciesChangedEvent();
    }

    /**
     * @covers ::run
     */
    final public function testPluginShouldReceiveEventWhenRun()
    {
        $exceptionName = $this->getCompatibleExceptionName('\\TypeError');

        $this->expectException($exceptionName);

        $this->setExpectedExceptionRegExp(
            $exceptionName,
            $this->regexMustBeAnInstanceOf('run', 'Composer\\Script\\Event')
        );

        Plugin::run();
    }

    /**
     * @covers ::run
     */
    final public function testPluginShouldRetrieveComposerFromEventWhenRun()
    {
        $this->markTestIncomplete('The "run" method calls "activate", write tests for that first.');

        $mockEvent = $this->getMockEvent();

        Plugin::run($mockEvent);
    }

    ////////////////////////////// MOCKS AND STUBS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * @param Composer $mockComposer
     * @param array $mockPackages
     */
    private function attachRepositoryManagerToComposer(Composer $mockComposer, $mockPackages)
    {
        /* @NOTE: Instead of also mocking Composer\Repository\WritableRepositoryInterface the RepositoryManager is re-used as a fluent interface */
        $mockRepositoryManager = $this->getMockBuilder('\\Composer\\Repository\\RepositoryManager')
            ->setMethods(array('getLocalRepository', 'getPackages', 'findPackages', 'getRepositoryManager'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mockRepositoryManager->expects($this->exactly(1))
            ->method('getLocalRepository')
            ->will($this->returnSelf())
        ;

        // $mockRepositoryManager->expects($this->exactly(1))
        //     ->method('getPackages')
        //     ->willReturn(array())
        // ;

        $mockRepositoryManager->expects($this->exactly(1))
            ->method('findPackages')
            ->with(Plugin::PACKAGE_NAME, null)
            ->willReturn($mockPackages)
        ;

        $mockComposer->expects($this->exactly(1))
            ->method('getRepositoryManager')
            ->willReturn($mockRepositoryManager)
        ;
    }

    /**
     * @param Composer $mockComposer
     */
    private function attachConfigToComposer(Composer $mockComposer)
    {
        $mockConfig = $this->getMockConfig();

        $mockComposer->expects($this->exactly(1))
            ->method('getConfig')
            ->willReturn($mockConfig)
        ;
    }

    /**
     * @return Composer|MockObject
     */
    private function getMockComposer()
    {
        return $this->getMockBuilder('\\Composer\\Composer')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return Config|MockObject
     */
    private function getMockConfig()
    {
        $mockConfig = $this->getMockBuilder('\\Composer\\Config')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mockConfig->expects($this->atLeastOnce())
            ->method('get')
            ->with('bin-dir')
            ->willReturn(self::MOCK_DIR);

        return $mockConfig;
    }

    /**
     * @param MockObject $mockConfig
     *
     * @return CommandEvent|MockObject
     */
    private function getMockEvent(MockObject $mockConfig = null)
    {
        $mockIo = $this->getMockIO();
        $mockComposer = $this->getMockComposer($mockConfig);

        $mockEvent = $this->getMockBuilder('\\Composer\\Script\\CommandEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $mockEvent->expects($this->exactly(1))
            ->method('getIO')
            ->willReturn($tmockIo)
        ;

        $mockEvent->expects($this->exactly(1))
            ->method('getComposer')
            ->willReturn($mockComposer)
        ;

        return $mockEvent;
    }

    /**
     * @return IOInterface|MockObject
     */
    private function getMockIO()
    {
        return $this->getMockBuilder('\\Composer\\IO\\IOInterface')->getMock();
    }

    /**
     * @return ProcessBuilder|MockObject
     */
    private function getMockProcessBuilder($count, $setPrefixCalled = true)
    {
        $mockProcessBuilder = $this->getMockBuilder('\\Symfony\\Component\\Process\\ProcessBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('setPrefix', 'getOutput', 'getProcess', 'mustRun', 'setArguments'))
            ->getMock()
        ;

        $mockProcessBuilder->expects($this->exactly((int) $setPrefixCalled))
            ->method('setPrefix')
            ->with(self::MOCK_DIR . DIRECTORY_SEPARATOR . 'phpcs')
        ;

        $mockProcessBuilder->expects($this->exactly($count))
            ->method('getOutput')
            ->willReturn('mock-output')
        ;

        $mockProcessBuilder->expects($this->exactly($count))
            ->method('getProcess')
            ->will($this->returnSelf())
        ;

        $mockProcessBuilder->expects($this->exactly($count))
            ->method('mustRun')
            ->will($this->returnSelf())
        ;

        $mockProcessBuilder->expects($this->exactly($count))
            ->method('setArguments')
            ->with(array('--config-show', Plugin::PHPCS_CONFIG_KEY))
            ->will($this->returnSelf())
        ;

        return $mockProcessBuilder;

    }

    /////////////////////////////// DATAPROVIDERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param string $methodName
     * @param string $parameterClassName
     * @param string $format
     *
     * @return string
     */
    private function buildRegexp($format, $methodName, $parameterClassName)
    {
        $className = $this->getClassUnderTest();
        $className = $this->escapeForRegexp($className);
        $parameterClassName = $this->escapeForRegexp($parameterClassName);
        return sprintf($format, $className, $methodName, $parameterClassName);
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private function escapeForRegexp($className)
    {
        return str_replace('\\', '\\\\', $className);
    }

    /**
     * @param string $methodName
     * @param string $parameterClassName
     *
     * @return string
     */
    private function regexMustBeAnInstanceOf($methodName, $parameterClassName)
    {
        $format = '/Argument [1-3]{1} passed to %1$s::%2$s\(\) must be an instance of %3$s, [a-zA-Z\\\\ ]+? given/';
        return $this->buildRegexp($format, $methodName, $parameterClassName);
    }

    /**
     * @return string
     */
    private function getClassUnderTest()
    {
        $className = get_called_class();
        return substr($className, 0, -strlen('Test'));
    }
}