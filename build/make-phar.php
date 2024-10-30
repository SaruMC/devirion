<?php

declare(strict_types=1);

if ($argc < 3) {
	fwrite(STDERR, "Usage: php make-phar.php <phar_name> <git_hash>\n");
	exit(1);
}

$pharName = $argv[1];
$gitHash = $argv[2];

define('PHAR_FILE', __DIR__ . '/../' . $pharName . '.phar');

const PLUGIN_FILE = __DIR__ . '/../plugin.yml';
const SRC_DIR = __DIR__ . '/../src';
const CLI_FILE = __DIR__ . '/../cli.php';

/**
 * Creates a PHAR file and adds files from the source and resource directories.
 *
 * @param string $gitHash The git hash to include in the PHAR metadata.
 */
function createPhar(string $gitHash) : void {
	if (file_exists(PHAR_FILE)) {
		unlink(PHAR_FILE); // Remove existing PHAR file
	}

	try {
		$phar = new Phar(PHAR_FILE);
		$phar->startBuffering();

		$phar->setStub($phar->createDefaultStub('src/poggit/virion/devirion/DEVirion.php'));

		addFilesToPhar($phar, SRC_DIR, "src/");
		addFileToPhar($phar, PLUGIN_FILE, "plugin.yml");
        addFileToPhar($phar, CLI_FILE, "cli.php");

		$phar->setMetadata([
			'git_hash' => $gitHash,
			'build_date' => date('Y-m-d H:i:s'),
		]);

		$phar->stopBuffering();
		echo "Successfully created " . PHAR_FILE . "\n";

	} catch (Exception $e) {
		fwrite(STDERR, "Failed to create " . PHAR_FILE . ": " . $e->getMessage() . "\n");
		exit(1);
	}
}

/**
 * Adds all files from a directory to a PHAR file.
 *
 * @param Phar   $phar      The PHAR object.
 * @param string $directory The directory to add files from.
 * @param string $prefix    Optional prefix for the file paths in the PHAR.
 */
function addFilesToPhar(Phar $phar, string $directory, string $prefix = '') : void {
	if (!is_dir($directory)) {
		return; // Skip if the directory does not exist
	}

	$directoryIterator = new RecursiveDirectoryIterator($directory);
	$iterator = new RecursiveIteratorIterator($directoryIterator);

	foreach ($iterator as $file) {
		if ($file->isFile()) {
			$relativePath = $prefix . str_replace($directory . '/', '', $file->getPathname());
			$phar->addFile($file->getPathname(), $relativePath);
		}
	}
}

/**
 * Adds a single file to the PHAR if it exists.
 *
 * @param Phar   $phar     The PHAR object.
 * @param string $filePath The path to the file.
 * @param string $pharPath The path in the PHAR.
 */
function addFileToPhar(Phar $phar, string $filePath, string $pharPath) : void {
	if (file_exists($filePath)) {
		$phar->addFile($filePath, $pharPath);
	}
}

createPhar($gitHash);