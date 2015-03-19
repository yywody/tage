<?php
namespace Tage\Tests\Runtime;

use \Tage\Runtime\FsBasedTplPreparer;

class FsBasedTplPreparerTest extends \PHPUnit_Framework_TestCase
{

    protected $tplDir;

    protected $compiledTplDir;

    protected $greetingTpl;

    protected function setUp()
    {
        parent::setUp();
        
        $this->tplDir = __DIR__ . '/_tpl';
        $this->compiledTplDir = __DIR__ . '/_compiledTpl';
        $this->greetingTpl = $this->tplDir . '/greeting.phtml';
    }
    
    protected function tearDown()
    {
        /**
         * @var \SplFileInfo[]
         */
        $di = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->compiledTplDir));
        $dirs = [];
        $files = [];
        foreach($di as $file)
        {
            if (strlen($file->getRealPath()) <= strlen($this->compiledTplDir) || strpos($file->getRealPath(), 'README.md'))
            {
                continue;
            }
            if ($file->isDir())
            {
                $dirs[] = $file->getRealPath();
            } else {
                $files[] = $file->getRealPath();
            }
        }
        foreach (array_merge($files, $dirs) as $f)
        {
            \unlink($file);
        }
        parent::tearDown();
    }

    public function test__construct()
    {
        $options = [
            'tplDir' => __DIR__,
            'compiledTplDir' => __DIR__
        ];
        
        new FsBasedTplPreparer($options, $this->mockOfCompiler());
    }

    /**
     * @dataProvider __construct_InvalidOptionsProvider
     * @expectedException Tage\Util\ConfigException
     */
    public function test__construct_InvalidOptionFields($options)
    {
        new FsBasedTplPreparer($options, $this->mockOfCompiler());
    }

    public function __construct_InvalidOptionsProvider()
    {
        return [
            [
                [
                    'tplDir' => __DIR__
                ]
            ],
            [
                [
                    'compiledTplDir' => __DIR__
                ]
            ],
            [
                []
            ]
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test__construct_InvalidOptions()
    {
        new FsBasedTplPreparer(null, $this->mockOfCompiler());
    }
    
    public function testPrepare()
    {
        $sourceFile = $this->tplDir.'/greeting.phtml';
        $compiled = $this->tplDir.'/greeting.phtml.compiled.php';
        $compiler = $this->mockOfCompiler();
        $compiler->expects($this->exactly(1))
            ->method('compile')
            ->with(file_get_contents($sourceFile))
            ->willReturn(file_get_contents($compiled));
        
        $options = [
            'tplDir' => $this->tplDir,
            'compiledTplDir' => $this->compiledTplDir,
        ];
        
        $preparer = new FsBasedTplPreparer($options, $compiler);
        $view = $preparer->prepare('greeting.phtml');
        $this->assertInstanceOf('Tage\Runtime\AbstractTemplate', $view);
        
        $view1 = $preparer->prepare('greeting.phtml');
        $this->assertInstanceOf('Tage\Runtime\AbstractTemplate', $view1);
    }

    /**
     *
     * @return \Tage\Compiler\Compiler
     */
    protected function mockOfCompiler()
    {
        return $this->getMockBuilder('\\Tage\\Compiler\\Compiler')
            ->setMethods([
            'compile'
        ])
            ->disableOriginalConstructor()
            ->getMock();
    }
}