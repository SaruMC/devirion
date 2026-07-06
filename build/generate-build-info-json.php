<?php

declare(strict_types=1);

if(count($argv) !== 7){
	fwrite(STDERR, "required args: <git hash> <tag name> <github repo (owner/name)> <build number> <github actions run ID> <phar name>\n");
	exit(1);
}

echo json_encode([
	"version" => $argv[2],
	"build" => (int) $argv[4],
	"git_commit" => $argv[1],
	"date" => time(),
	"details_url" => "https://github.com/$argv[3]/releases/tag/$argv[2]",
	"download_url" => "https://github.com/$argv[3]/releases/download/$argv[2]/$argv[6].phar",
	"source_url" => "https://github.com/$argv[3]/tree/$argv[2]",
	"build_log_url" => "https://github.com/$argv[3]/actions/runs/$argv[5]",
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
