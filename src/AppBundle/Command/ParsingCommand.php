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

        $this->recursionSite('http://api.symfony.com/3.4/Symfony.html', 'Symfony', null, 0);

        $output->writeln([
            'Finish',
        ]);
    }

    public function recursionSite($urlCurrent, $nameCurrent, $parentID, $level)
    {
        $regexCSSdirectory = "div.namespace-list > a";
        $regexCSSclass = "div.row > div.col-md-6 > a";
        $regexCSSinterface = "div.row > div.col-md-6 > em > a";

        $pageItemClass = new ClassSymfony();
        $pageItemInterface = new InterfaceSymfony();

        //парсинг очередного неймспейса в базу данных$namespace
        $namespace = new NamespaceSymfony();
        $namespace->setName($nameCurrent);
        $namespace->setUrl($urlCurrent);
        $namespace->setParent($parentID);
        $namespace->setLvl($level);
        $this->em->persist($namespace);

        $nodes = $this->createNodes($urlCurrent, $regexCSSdirectory);

        //раскрутка до последнего namespace
        foreach ($nodes as $item) {
            $nameCurrent = $item->nodeValue;
            $urlChild = 'http://api.symfony.com/3.4/' . str_replace('../', '', $item->getAttribute("href"));
            $this->recursionSite($urlChild, $nameCurrent, $namespace, $level);
        }

        //парсинг в базу данных  класов и интерфейсов
        $this->parseTree($urlCurrent, $regexCSSclass, $namespace, $pageItemClass);
        $this->parseTree($urlCurrent, $regexCSSinterface, $namespace, $pageItemInterface);

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
