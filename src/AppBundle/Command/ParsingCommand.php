<?php

namespace AppBundle\Command;

use AppBundle\Entity\ClassSymfony;
use AppBundle\Entity\InterfaceSymfony;
use AppBundle\Entity\NamespaceSymfony;
use AppBundle\Entity\PageItemInterface;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ParsingCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    private $em;

    protected function configure()
    {
        $this
            ->setName('app:parse_api')
            ->setDescription('Parsing API Symfony');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $output->writeln([
            'Start parsing Symfony site',
            '==========================',
        ]);

        $this->recursionSite('http://api.symfony.com/3.4/Symfony.html', 'Symfony');

        $output->writeln([
            'Finish',
        ]);
    }

    public function recursionSite($urlCurrent, $nameDirectory)
    {
        $regexCSSdirectory = "div.namespace-list > a";
        $regexCSSclass = "div.row > div.col-md-6 > a";
        $regexCSSinterface = "div.row > div.col-md-6 > em > a";

        $pageItemClass = new ClassSymfony();
        $pageItemInterface = new InterfaceSymfony();

        $namespace = new NamespaceSymfony();
        $namespace->setName($nameDirectory);
        $namespace->setUrl($urlCurrent);
        $this->em->persist($namespace);

        $nodes = $this->createNodes($urlCurrent, $regexCSSdirectory);

        foreach ($nodes as $item) {
            $name = $item->textContent;
            $urlChild = 'http://api.symfony.com/3.4/' . str_replace('../', '', $item->getAttribute("href"));
            $this->recursionSite($urlChild, $name);
        }

        $this->parseTree($urlCurrent, $regexCSSclass, $nameDirectory, $pageItemClass);
        $this->parseTree($urlCurrent, $regexCSSinterface, $nameDirectory, $pageItemInterface);
        $this->em->flush();
    }

    public function createNodes($url, $xpath)
    {
        $client = new Client();
        $request = $client->get($url);
        $page = (string)$request->getBody();
        $crawler = new Crawler($page);
        $nodes = $crawler->filter($xpath);

        return $nodes;
    }

    public function parseTree($url, $xpath, NamespaceSymfony $namespace, PageItemInterface $pageItem)
    {
        $nodes = $this->createNodes($url, $xpath);

        foreach ($nodes as $node) {
            $name = $node->textContent;
            $url = 'http://api.symfony.com/3.4/' . str_replace('../', '', $node->getAttribute("href"));
            $pageItem->setName($name);
            $pageItem->setUrl($url);
            $pageItem->setNamespace($namespace);
            $this->em->persist($pageItem);
        }
    }
}
