<?php

namespace App\Command;

use SymfonyBundler\CommandController;

class CreateController extends CommandController
{
    public function run($argv)
    {
        if(!isset($argv[2])){
            $this->getApp()->getPrinter()->display("\033[31mInvalid bundle name");
            die();
        }
        $name = $argv[2];
        $name = ucwords(strtolower($name));
        $bundleName = "{$name}Bundle";
        chdir('../');
        $desktop = getcwd();
        $bundleDir = "$desktop/$bundleName";
        if (is_dir($bundleDir)){
            $this->getApp()->getPrinter()->display("\033[31mFolder already exists");
            die();
        }
        if (!mkdir($bundleDir, 0777, true)) {
            die();
        }
        $srcDir = "$bundleDir/src";
        if (!mkdir($srcDir, 0777, true)) {
            die();
        }
        $confDir = "$srcDir/DependencyInjection";
        if (!mkdir($confDir, 0777, true)) {
            die();
        }
        $resDir = "$srcDir/Resources/config";
        if (!mkdir($resDir, 0777, true)) {
            die();
        }
        $testDir = "$bundleDir/tests";
        if (!mkdir($testDir, 0777, true)) {
            die();
        }
        echo "\n";
        $organization = readline("\033[33mOrganization Name: \033[0m");
        if(!isset($organization) || empty($organization)){
            $this->getApp()->getPrinter()->display("\033[31mInvalid organization name");
            die();
        }
        $organization = ucwords(strtolower($organization));
        $this->createBundleClass($bundleName,$organization,$srcDir);
        $this->createConfigurationClass($name,$organization,$confDir);
        $this->createExtensionClass($name,$organization,$confDir);
        $this->createConfigFiles($resDir);

    }

    private function createBundleClass($bundleName,$org,$srcDir){
        $class = "$org$bundleName";
        $myfile = fopen("$srcDir/$class.php", "w") or die("Unable to open file!");
        $txt =
        "
<?php

namespace $org\\$bundleName;
use Symfony\\Component\\HttpKernel\\Bundle\\Bundle;
        
class $class extends Bundle{
        
}
        ";
        fwrite($myfile, $txt);
        fclose($myfile);

    }
    private function createConfigurationClass($name,$org,$srcDir){
        $class = "Configuration";
        $bundleName = "{$name}Bundle";
        $lowerCaseName = strtolower($name);
        $lowerCaseOrg = strtolower($org);
        $treeBuilderName = "{$lowerCaseOrg}_$lowerCaseName";
        $myfile = fopen("$srcDir/$class.php", "w") or die("Unable to open file!");
        $txt =
        "
<?php


namespace $org\\$bundleName\\DependencyInjection;
        
        
use Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder;
use Symfony\\Component\\Config\\Definition\\ConfigurationInterface;
        
class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        \$treeBuilder = new TreeBuilder('$treeBuilderName');

        if (method_exists(\$treeBuilder, 'getRootNode')) {
            \$rootNode = \$treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            \$rootNode = \$treeBuilder->root('$treeBuilderName');
        }
        \$rootNode->children()
            ->booleanNode('test')->defaultTrue()->end()
            ->end();
        return \$treeBuilder;
    }
}
        ";
        fwrite($myfile, $txt);
        fclose($myfile);

    }
    private function createExtensionClass($name,$org,$srcDir){
        $class = "{$org}{$name}Extension";
        $bundleName = "{$name}Bundle";
        $myfile = fopen("$srcDir/$class.php", "w") or die("Unable to open file!");
        $txt =
        "
<?php


namespace $org\\$bundleName\\DependencyInjection;
        
        
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class {$org}{$name}Extension extends Extension
{

    public function load(array \$configs, ContainerBuilder \$container)
    {

        \$loader= new XmlFileLoader(\$container,new FileLocator(__DIR__.'/../Resources/config'));
        \$loader->load('services.xml');
        \$configuration = \$this->getConfiguration(\$configs,\$container);
        \$config = \$this->processConfiguration(\$configuration,\$configs);
    }
}
        ";
        fwrite($myfile, $txt);
        fclose($myfile);        
    }

    private function createConfigFiles($dir){
        //create services.xml file        
        $myfile = fopen("$dir/services.xml", "w") or die("Unable to open file!");
        $txt =
        "
<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<container xmlns=\"http://symfony.com/schema/dic/services\"
            xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
            xsi:schemaLocation=\"http://symfony.com/schema/dic/services
        https://symfony.com/schema/dic/services/services-1.0.xsd\">
    <imports>
        <import resource=\"parameters.xml\" />
    </imports>
    <services>

        <!--services-->

        <!--repositories-->

        <!--controllers-->

        <!--listeners-->

        <!--events-->



    </services>
</container>
        ";
        fwrite($myfile, $txt);
        fclose($myfile);          
        
        //create parameters.xml file
        $myfile = fopen("$dir/parameters.xml", "w") or die("Unable to open file!");
        $txt =
        "
<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<container xmlns=\"http://symfony.com/schema/dic/services\"
            xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
            xsi:schemaLocation=\"http://symfony.com/schema/dic/services
        https://symfony.com/schema/dic/services/services-1.0.xsd\">
    <parameters>

    </parameters>
</container>
        ";
        fwrite($myfile, $txt);
        fclose($myfile);  

        //create routes.xml file
        $myfile = fopen("$dir/routes.xml", "w") or die("Unable to open file!");
        $txt =
        "
<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<routes xmlns=\"http://symfony.com/schema/routing\"
        xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
        xsi:schemaLocation=\"http://symfony.com/schema/routing
        https://symfony.com/schema/routing/routing-1.0.xsd\">

</routes>
        ";
        fwrite($myfile, $txt);
        fclose($myfile);  
    }
}