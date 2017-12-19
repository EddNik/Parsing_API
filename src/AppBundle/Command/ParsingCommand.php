<?php

namespace AppBundle\Command;

use AppBundle\Entity\ClassSymfony;
use AppBundle\Entity\InterfaceSymfony;
use AppBundle\Entity\NamespaceSymfony;
use GuzzleHttp\Client;
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
            ->setDescription('Parsing API Symfony');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Start parsing Symfony site',
            '==========================',
        ]);

        $this->recursionSite('http://api.symfony.com/3.4/Symfony.html', 'Symfony');

        $output->writeln([
            'Finish',
        ]);
    }

    public function recursionSite($url, $name)
    {
        $client = new Client();
        $request = $client->get($url);
        $homepage = (string) $request->getBody();
        $crawler = new Crawler($homepage);
        $namespacesPage = $crawler->filter('div.namespace-list > a');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $namespaces = new NamespaceSymfony();
        $namespaces
            ->setName($name)
            ->setUrl($url);
        $em->persist($namespaces);

        foreach ($namespacesPage as $index) {
            $name = $index->textContent;
            $urlChild = 'http://api.symfony.com/3.4/' . str_replace('../', '', $index->getAttribute("href"));
            $this->recursionSite($urlChild, $name);
        }

        $request = $client->get($url);
        $childPage = (string) $request->getBody();
        $crawler = new Crawler($childPage);

        $classesPage = $crawler->filter('div.row > div.col-md-6 > a');
        foreach ($classesPage as $index) {
            $name = $index->textContent;
            $url = 'http://api.symfony.com/3.4/' . str_replace('../', '', $index->getAttribute("href"));
            $classes = new ClassSymfony();
            $classes
                ->setName($name)
                ->setUrl($url)
                ->setNamespace($namespaces);
            $em->persist($classes);
        }

        $interfacesPage = $crawler->filter('div.row > div.col-md-6 > em > a');
        foreach ($interfacesPage as $index) {
            $name = $index->textContent;
            $url = 'http://api.symfony.com/3.4/' . str_replace('../', '', $index->getAttribute("href"));
            $interfaces = new InterfaceSymfony();
            $interfaces
                ->setName($name)
                ->setUrl($url)
                ->setNamespace($namespaces);
            $em->persist($interfaces);
        }

        unset($crawler);
        $em->flush();
    }
}
