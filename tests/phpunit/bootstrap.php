<?php

use Civi\RemoteTools\EntityProfile\TestRemoteProductReadOnlyEntityProfile;
use Civi\RemoteTools\EntityProfile\TestRemoteProductReadWriteEntityProfile;
use Composer\Autoload\ClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

ini_set('memory_limit', '2G');
ini_set('safe_mode', 0);

require_once __DIR__ . '/../../vendor/autoload.php';

// Make CRM_Remotetools_ExtensionUtil available.
require_once __DIR__ . '/../../remotetools.civix.php';

// phpcs:disable
eval(cv('php:boot --level=classloader', 'phpcode'));
// phpcs:enable

// phpcs:disable PSR1.Files.SideEffects

// Allow autoloading of PHPUnit helper classes in this extension.
$loader = new ClassLoader();
$loader->add('CRM_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('Civi\\', [__DIR__ . '/../../Civi', __DIR__ . '/Civi']);
$loader->add('api_', [__DIR__ . '/../..', __DIR__]);
$loader->addPsr4('api\\', [__DIR__ . '/../../api', __DIR__ . '/api']);
$loader->register();

// Ensure function ts() is available - it's declared in the same file as CRM_Core_I18n
\CRM_Core_I18n::singleton();

function _remotetools_test_civicrm_container(ContainerBuilder $container): void {
  $container->autowire(TestRemoteProductReadOnlyEntityProfile::class)
    ->addTag(TestRemoteProductReadOnlyEntityProfile::SERVICE_TAG);

  $container->autowire(TestRemoteProductReadWriteEntityProfile::class)
    ->addTag(TestRemoteProductReadWriteEntityProfile::SERVICE_TAG);
}

/**
 * Call the "cv" command.
 *
 * @param string $cmd
 *   The rest of the command to send.
 * @param string $decode
 *   Ex: 'json' or 'phpcode'.
 * @return string
 *   Response output (if the command executed normally).
 * @throws \RuntimeException
 *   If the command terminates abnormally.
 */
function cv($cmd, $decode = 'json') {
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => STDERR);
  $oldOutput = getenv('CV_OUTPUT');
  putenv("CV_OUTPUT=json");

  // Execute `cv` in the original folder. This is a work-around for
  // phpunit/codeception, which seem to manipulate PWD.
  $cmd = sprintf('cd %s; %s', escapeshellarg(getenv('PWD')), $cmd);

  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__);
  putenv("CV_OUTPUT=$oldOutput");
  fclose($pipes[0]);
  $result = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  if (proc_close($process) !== 0) {
    throw new RuntimeException("Command failed ($cmd):\n$result");
  }
  switch ($decode) {
    case 'raw':
      return $result;

    case 'phpcode':
      // If the last output is /*PHPCODE*/, then we managed to complete execution.
      if (substr(trim($result), 0, 12) !== "/*BEGINPHP*/" || substr(trim($result), -10) !== "/*ENDPHP*/") {
        throw new \RuntimeException("Command failed ($cmd):\n$result");
      }
      return $result;

    case 'json':
      return json_decode($result, 1);

    default:
      throw new RuntimeException("Bad decoder format ($decode)");
  }
}
