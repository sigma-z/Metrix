<?php declare(strict_types=1);

namespace SigmaZ\Metrix;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    protected static $defaultName = 'metrix:run';

    protected function configure()
    {
        $this
            ->setDescription('Rudimentary command to analyse pDepend summary.xml')
            ->setHelp('This command finds entries by conditions from a pDepend summary.xml')
        ;

        $this
            ->addArgument('summaryFile', InputArgument::REQUIRED, 'file (pdepend summary.xml)')
            ->addArgument('xpath', InputArgument::REQUIRED, "xpath, ie: 'package[].class[].@attributes.lloc'")
            ->addArgument('condition', InputArgument::REQUIRED, "condition, ie: '>0'")
            ->addArgument('sortOrder', InputArgument::OPTIONAL, 'sort order: [DESC] or ASC', CodeMetric::SORT_DESC)
            ->addArgument('limit', InputArgument::OPTIONAL, 'limit the number of result displayed', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $condition = $input->getArgument('condition');
        preg_match('/^([<=>]{1,2})\s*(\d+)$/', $condition, $conditionMatches);
        if (count($conditionMatches) !== 3) {
            throw new InvalidArgumentException("Condition must contain an operator and a number, like '>1000'");
        }
        list(, $conditionOperator, $conditionNumber) = $conditionMatches;

        $summaryFile = $input->getArgument('summaryFile');
        $sortOrder = $input->getArgument('sortOrder');
        $xpath = $input->getArgument('xpath');
        $limit = $input->getArgument('limit');

        $metric = new CodeMetric($xpath);
        $xpathParts = explode('.', $xpath);
        $field = end($xpathParts);

        $metric->addCondition(new Condition($conditionNumber, $conditionOperator))
            ->setSortInfo($field, $sortOrder) // sorted descending
            ->setResultLimit($limit);

        $pDependSummary = new pDependSummary();
        $pDependSummary->loadFromFile($summaryFile);
        $result = $pDependSummary->fetchMetric($metric);

        $output->writeln([
            "Found entries for $xpath $condition ORDER BY $sortOrder LIMIT $limit",
            "Summary file: $summaryFile",
            str_repeat('=', 40),
            '',
        ]);
        foreach ($result as $index => $entry) {
            $entryOutputLines = ['Position:' . ($index + 1)];
            if (isset($entry['package'])) {
                $entryOutputLines[] = 'Package: ' . $entry['package'];
            }
            if (isset($entry['class'])) {
                $entryOutputLines[] = 'Class: ' . $entry['class'];
            }
            if (isset($entry['file'])) {
                $entryOutputLines[] = 'File: ' . $entry['file'];
            }
            $entryOutputLines[] = "$field: " . $entry[$field];
            $entryOutputLines[] = '';

            $output->writeln($entryOutputLines);
        }
    }
}
