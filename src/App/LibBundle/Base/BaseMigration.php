<?php

namespace App\LibBundle\Base;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;


abstract class BaseMigration extends AbstractMigration implements ContainerAwareInterface {
    
    protected $container;

    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager() {
        return $this->container->get("doctrine.orm.entity_manager");
    }

    public function up(Schema $schema) {
        
    }
    public function down(Schema $schema) {
        
    }
    
    
}
