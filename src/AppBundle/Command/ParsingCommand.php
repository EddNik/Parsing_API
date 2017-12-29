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
        $xpathNamespace = "div.namespace-list > a";
        $xpathClass = "div.row > div.col-md-6 > a";
        $xpathInterface = "div.row > div.col-md-6 > em > a";

        //парсинг очередного неймспейса в базу данных$namespace
        $namespace = new NamespaceSymfony();
        $namespace->setName($nameCurrent);
        $namespace->setUrl($urlCurrent);
        $namespace->setParent($parentID);
        $namespace->setLvl($level);
        $this->em->persist($namespace);

        $page = $this->getPage($urlCurrent);

        //раскрутка до последнего namespace
        foreach ($page->filter($xpathNamespace) as $item) {
            $nameCurrent = $item->nodeValue;
            $urlChild = 'http://api.symfony.com/3.4/' . str_replace('../', '', $item->getAttribute("href"));
            $this->recursionSite($urlChild, $nameCurrent, $namespace, $level);
        }

        //парсинг в базу данных  класов и интерфейсов
        $this->parseTree($page, $xpathClass, $namespace, function () {
            return new ClassSymfony();
        });
        $this->parseTree($page, $xpathInterface, $namespace, function () {
            return new InterfaceSymfony();
        });

        $this->em->flush();
    }

    public function getPage($url)
    {
        $client = new Client();
        $request = $client->get($url);
        $page = (string)$request->getBody();
        $crawler = new Crawler($page);
        return $crawler;
    }

    public function parseTree(Crawler $page, $xpath, NamespaceSymfony $namespace, callable $pageItemCallback)
    {
        foreach ($page->filter($xpath) as $node) {
            $name = $node->textContent;
            $url = 'http://api.symfony.com/3.4/' . str_replace('../', '', $node->getAttribute("href"));
            /**
             * @var PageItemInterface $pageItem
             */
            $pageItem = $pageItemCallback();
            $pageItem->setName($name);
            $pageItem->setUrl($url);
            $pageItem->setNamespace($namespace);
            $this->em->persist($pageItem);
        }
    }
}
