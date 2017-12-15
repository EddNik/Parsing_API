<?php

namespace AppBundle\Command;


use AppBundle\Entity\ClassSymfony;
use AppBundle\Entity\InterfaceSymfony;
use AppBundle\Entity\NamespaceSymfony;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ParsingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:parse_api')
            ->setDescription('Parsing API Symfony')
            ->setHelp('This command allows you parsing http://api.symfony.com/3.3/index.html');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Start parsing Symfony site',
            '==========================',
            'проверка получения контента',
        ]);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $homepage = file_get_contents('http://api.symfony.com/3.4/index.html');
        //var_dump($homepage);
        $crawler = new Crawler($homepage);
        $readPage = $crawler->filter('div.namespace-container > ul > li > a');
        foreach ($readPage as $index) {
            $namespace = new NamespaceSymfony();
            $namespace->setName($index->textContent);
            $namespace->setUrl($index->getAttribute('href'));
            $em->persist($namespace);
            $childPageURL = 'http://api.symfony.com/3.4/' . preg_replace('/..\/..\//', '', $index->getAttribute('href'));
            $childPageContext = file_get_contents($childPageURL);
            //var_dump($childPageContext);
            $crawler = new Crawler($childPageContext);
            $readPage_class = $crawler->filter('div.row > div.col-md-6 > a');
            foreach ($readPage_class as $index_class) {
                $class = new ClassSymfony();
                $class->setName($index_class->textContent);
                $class->setUrl($childPageURL);
                $class->setNamespace($namespace);
                $em->persist($class);
            }
            $readPage_interface = $crawler->filter('div.row > div.col-md-6 > em > a');
            foreach ($readPage_interface as $index_interface) {
                $interface = new InterfaceSymfony();
                $interface->setName($index_interface->textContent);
                $interface->setUrl($childPageURL);
                $interface->setNamespace($namespace);
                $em->persist($interface);
            }
        }
        $em->flush();
    }
}