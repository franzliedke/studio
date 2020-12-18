<?php declare(strict_types=1);

namespace Tests\Studio\Runner;

use Composer\Json\JsonManipulator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class CliTest extends TestCase
{
	private $fixturesDir;
	private $composer;
    private $buildDir;


    /**
     * @inheritDoc
     */
    public function setUp ()
    {
        $this->composer = "composer";
        $this->fixturesDir = \dirname(__DIR__) . "/fixtures";
        $this->buildDir = "{$this->fixturesDir}/.build";

        // Removes the build dir
        $filesystem = new Filesystem();
        $filesystem->remove($this->buildDir);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown ()
    {
        // Removes the build dir
        $filesystem = new Filesystem();
        $filesystem->remove($this->buildDir);
    }


    /**
	 */
	public function testList ()
	{
        $studioExecutable = $this->installFixture("list");

		$output = (new Process([
            $studioExecutable,
			"list"
		], $this->buildDir))
			->mustRun()
			->getOutput();

		self::assertStringMatchesFormat("studio %d.%d.%d%a", $output);
	}


	/**
	 */
	public function testLoad ()
	{
		$studioExecutable = $this->installFixture("load");

		$output = (new Process([
            $studioExecutable,
			"load",
			"./sub-project"
		], $this->buildDir))
			->mustRun()
			->getOutput();

		$config = \json_decode(
			\file_get_contents("{$this->buildDir}/studio.json"),
			true
		);

		if (\json_last_error())
		{
			self::assertTrue(false, "JSON parsing failed");
		}

		// should be in the studio.json
		self::assertContains("./sub-project", $config["paths"]);

		// should install the package
		// first: add the package manually
		$composerJsonPath = "{$this->buildDir}/composer.json";
		$package = new JsonManipulator(\file_get_contents($composerJsonPath));
		$package->addSubNode("require", "franzl/studio-example", "^1.0");
		\file_put_contents($composerJsonPath, $package->getContents());

		$output = (new Process([
			$this->composer,
			"update",
		], $this->buildDir))
			->mustRun()
            ->getErrorOutput();

		self::assertStringContainsString("[Studio] Loading path ./sub-project", $output);
		self::assertDirectoryExists("{$this->buildDir}/vendor/franzl/studio-example");
	}




	/**
	 * Installs the fixture and returns the path to the studio executable
	 */
	private function installFixture (string $fixtureName) : string
	{
		$fixturePath = "{$this->fixturesDir}/{$fixtureName}";

		// copy files to build dir
		$filesystem = new Filesystem();
		$filesystem->mirror($fixturePath, $this->buildDir);

		// install studio
		$process = new Process([
			$this->composer,
			"install"
		], $this->buildDir);

		$process->mustRun();

		return "{$this->buildDir}/bin/studio";
	}
}
