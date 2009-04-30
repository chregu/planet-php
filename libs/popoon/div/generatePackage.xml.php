<?php
/**
* A simple example
* @package PEAR_PackageFileManager
*/
/**
* Include the package file manager
*/
require_once('PEAR/PackageFileManager.php');
$test = new PEAR_PackageFileManager();
if (PEAR::isError($test)) {
    echo $test->getMessage();
    exit;
}
$bla = $test->setOptions(
array('baseinstalldir' => 'popoon',
'version' => '1.0',
'packagedirectory' => '.',
'packagefile' => 'package.xml',
'package' => 'popoon',
'state' => 'stable',
'simpleoutput' => true,
'license' => 'ASFL-2.0',

'filelistgenerator' => 'cvs',
'notes' => 'First official stable release of Popoon',
'ignore' => array('package.xml','api/')));
//$test->addDependency('PEAR', '1.1');
if (PEAR::isError($bla)) {
    echo $bla->getMessage();
}

$test->addMaintainer("chregu","lead","Christian Stocker","chregu@bitflux.org");

$e = $test->writePackageFile();
if (PEAR::isError($e)) {
    echo $e->getMessage();
}
?>