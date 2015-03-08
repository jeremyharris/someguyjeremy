<?php

require 'vendor/autoload.php';

$climate = new League\CLImate\CLImate();
$climate->description('Build and deploy the site');
$climate->arguments->add([
	'site' => [
        'prefix' => 's',
        'longPrefix' => 'site',
        'description' => 'Site target',
		'required' => true,
    ],
	'build' => [
        'prefix' => 'b',
        'longPrefix' => 'build',
        'description' => 'Build target',
		'required' => true,
    ],
    'force' => [
        'prefix' => 'f',
        'longPrefix' => 'force',
        'description' => 'Force complete rebuild',
		'noValue' => true,
    ],
    'deploy' => [
        'prefix' => 'd',
        'longPrefix' => 'deploy',
        'description' => 'Upload site to S3',
		'noValue' => true,
    ],
    'help' => [
        'prefix' => 'h',
        'longPrefix' => 'help',
        'description' => 'Print a usage statement',
        'definedOnly' => true,
    ],
]);

if ($climate->arguments->defined('help')) {
    $climate->usage();
    exit();
}

try {
    $climate->arguments->parse();
} catch (Exception $e) {
    $climate->error($e->getMessage());
    $climate->br();
    $climate->usage();
    exit();
}

$sitePath = realpath($climate->arguments->get('site'));
$buildPath = realpath($climate->arguments->get('build'));

$climate->out('Building site...');

$build = new JeremyHarris\Build\Build($sitePath, $buildPath);
$build->build($climate->arguments->get('force'));

$climate->green('Done!');

if (!$climate->arguments->get('deploy')) {
    exit();
}

$builtFiles = $build->getBuiltFiles();

$config = parse_ini_file('config.ini');

if ($config === false) {
    $climate->error('Error parsing config.ini');
    exit();
}
$s3 = Aws\S3\S3Client::factory([
    'key' => $config['aws_access'],
    'secret' => $config['aws_secret'],
    'region' => $config['aws_region'],
]);

$climate->out('Uploading new files...');
$progress = $climate->progress()->total(count($builtFiles));

$errors = [];
foreach ($builtFiles as $i => $filepath) {
    $progress->current($i+1);
    $relativePath = ltrim(str_replace($buildPath, '', $filepath), '/');
    try {
        $s3->putObject([
            'Bucket' => $config['aws_bucket'],
            'Key' => $relativePath,
            'Body' => fopen($filepath, 'r'),
            'ACL' => 'public-read',
        ]);
    } catch (Aws\S3\Exception\S3Exception $e) {
        $errors[] = $relativePath . ': ' . $e->getMessage();
    }
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        $climate->error($error);
    }
}

$climate->green('Done!');