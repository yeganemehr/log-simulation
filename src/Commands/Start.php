<?php
namespace Yeganemehr\LogSimulation\Commands;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Yeganemehr\LogSimulation\LogShower;

class Start extends Command
{
    protected static $defaultName = 'log-simulation:start';

    protected function configure(): void
    {
        $this
            ->addArgument('src', InputArgument::REQUIRED, 'Path to source log file.')
            ->addArgument('dst', InputArgument::REQUIRED, 'Path to destination log file.')
            ->addOption('speed', 's', InputArgument::OPTIONAL, 'Speed rate in compare to original log entries.', 1)
            ->addOption('purge', 'p', InputArgument::OPTIONAL, 'Purge the destination file before start',false)
            ->addOption('max-lines', null, InputArgument::OPTIONAL, 'Max lines in console (0 = unlimited)', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
		if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }


		$src = $input->getArgument('src');
		if (!is_file($src)) {
			throw new Exception("'{$src}' is not a file");
		}
		if (!is_readable($src)) {
			throw new Exception("'{$src}' is not a readable");
		}

		$dst = $input->getArgument('dst');
		if (is_file($dst) and !is_writeable($dst)) {
			throw new Exception("'{$dst}' is not a writeable file");
		}

		$speed = floatval($input->getOption('speed'));
		if ($speed <= 0) {
			throw new Exception("speed must be a positive number");
		}

		$purge = boolval($input->getOption('purge'));
		$maxLines = intval($input->getOption('max-lines'));

		$srcFd = fopen($src, 'r');
		if ($srcFd === false) {
			throw new Exception("Cannot open '{$fd}' for read");
		}

		$dstFd = fopen($dst, $purge ? 'w' : 'a');
		if ($dstFd === false) {
			throw new Exception("Cannot open '{$fd}' for write");
		}

		$logShower = new LogShower($output->section(), $maxLines);
		$lastTimestamp = null;
        while (($line = stream_get_line($srcFd, 10 * 1024, PHP_EOL)) !== false) {
			$time = $this->extractTime($line);
			if ($lastTimestamp !== null and $time > $lastTimestamp) {
				usleep(intval(($time - $lastTimestamp) * $speed * 1000000));
			}
			$lastTimestamp = $time;
			fwrite($dstFd, $line . PHP_EOL);
			$logShower->append($line);

		}
		fclose($srcFd);
		fclose($dstFd);


		return Command::SUCCESS;
    }

	protected function extractTime(string $line): ?int {
		if (!preg_match("#\[(?P<time>\d{2}/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/\d{4}:\d{2}:\d{2}:\d{2} (?:-|\+)\d{4})\]#", $line, $matches)) {
			return null;
		}
		$time = strtotime($matches['time']);
		if ($time === false) {
			return null;
		}
		return $time;
	}
}
