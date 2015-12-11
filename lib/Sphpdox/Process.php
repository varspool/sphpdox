<?php

namespace Sphpdox;

use Sphpdox\Element\NamespaceElement;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use \InvalidArgumentException;

class Process extends Command
{
    /**
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('namespace', InputArgument::REQUIRED, 'The namespace to process'));
        $definition->addArgument(new InputArgument('path', InputArgument::REQUIRED, 'The path the namespace can be found in'));
        $definition->addOption(new InputOption('output', 'o', InputOption::VALUE_REQUIRED, 'The path to output the ReST files', 'build'));
        $definition->addOption(new InputOption('title', 't', InputOption::VALUE_REQUIRED, 'An alternate title for the top level namespace', null));
        $definition->addOption(new InputOption('exclude', 'x', InputOption::VALUE_REQUIRED, 'Semicolon separated namespaces to ignore', null));
        $definition->addOption(new InputOption('filters', 'f', InputOption::VALUE_REQUIRED, 'Semicolon separated filename filters to apply', null));

        $this
            ->setName('process')
            ->setDescription('Processes a directory of PHPDoc documented code into ReST')
            ->setHelp('The process command works recursively on a directory of PHP code.')
            ->setDefinition($definition);
    }

    /**
     * @see Symfony\Component\Console\Command.Command::interact()
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('namespace')) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new Question('Namespace of code to document:');
            $question->setValidator(function($namespace) {
                $namespace = trim($namespace);

                if (!$namespace) {
                    throw new InvalidArgumentException('Invalid namespace');
                }

                return $namespace;
            });

            $namespace = $helper->ask($input, $output, $question);

            $input->setArgument('namespace', $namespace);
        }

        $path = $input->getArgument('path');

        if (!$path || !is_readable($path)) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new Question('Path to namespace: ');
            $question->setValidator(function ($path) {
                $path = trim($path);

                if (!$path || !is_readable($path) || !is_dir($path)) {
                    throw new InvalidArgumentException('Invalid path to namespace source');
                }

                return $path;
            });

            $path = $helper->ask($input, $output, $question);

            $input->setArgument('path', $path);
        }

        $out = $input->getOption('output');

        if ($out == 'build' && !file_exists($out)) {
            mkdir($out);
        }

        if (!is_writable($out)) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new Question(sprintf('Path to output built files [%s]: ', $out));
            $question->setValidator(function ($out) {
                $out = trim($out);

                if (!$out || !is_writable($out)) {
                    throw new \InvalidArgumentException('Invalid output path');
                }
            });

            $out = $helper->ask($input, $output, $question);
            $input->setOption('output', $out);
        }
    }

    /**
     * @see Symfony\Component\Console\Command.Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $namespace = $input->getArgument('namespace');
        $path = $input->getArgument('path');
        $out  = realpath($input->getOption('output'));

        $output->writeln(sprintf('<comment>Processing code from namespace %s</comment>', $namespace));
        $output->writeln(sprintf('<comment>Processing files from %s</comment>', $path));
        $output->writeln(sprintf('<comment>Outputting to %s</comment>', $out));

        $output_files = array();

        $filters = array();
        if (($filtersArgument = trim($input->getOption('filters')))) {
            $filters = explode(';', $filtersArgument);
            foreach ($filters as $filter) {
                $output->writeln(sprintf('<comment>Applying filter %s</comment>', $filter));
            }
        }

        $broker = new Broker($backend = new Memory());
        $broker->processDirectory($path, $filters);

        $namespaces = $backend->getNamespaces();
        ksort($namespaces);

        $excludes = array();
        if (($exclude = trim($input->getOption('exclude')))) {
            $excludes = explode(';', $exclude);
            foreach ($excludes as $exclude) {
                $output->writeln(sprintf('<comment>Excluding code from %s</comment>', $exclude));
            }
        }

        $filtered = array();

        foreach ($namespaces as $n => $reflection) {
            if (substr($n, 0, strlen($namespace)) != $namespace) continue;

            foreach ($excludes as $exclude) {
                if (strncmp($n, $exclude, strlen($exclude)) == 0) {
                    continue 2;
                }
            }

            $filtered[$n] = $reflection;
        }

        unset($namespaces);
        $elements = array();

        foreach ($filtered as $n => $reflection) {
            $output->writeln(sprintf('<info>Processing %s</info>', $n));

            $element = new NamespaceElement($reflection);
            $element->buildClasses($out, $output);

            $elements[$n] = $element;
        }

        unset($filtered);

        foreach ($elements as $n => $element) {
            $output->writeln(sprintf('<info>Building index for %s</info>', $n));

            $options = array();

            if ($n == $namespace && $input->getOption('title')) {
                $options = array('title' => $input->getOption('title'));
            }

            $element->buildIndex($out, $output, $options);
        }
    }
}
