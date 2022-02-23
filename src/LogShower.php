<?php
namespace Yeganemehr\LogSimulation;

use Exception;
use  Symfony\Component\Console\Output\ConsoleSectionOutput;

class LogShower {
	/**
	 * @var string[]
	 */
	protected array $lines = [];
	public function __construct(
		public ConsoleSectionOutput $output,
		public int $maxLines
	) {
		if ($maxLines < 0) {
			throw new Exception("max lines are negetive, it's meaningless");
		}
	}

	public function append(string $line): void {
		if ($this->maxLines == 0) {
			$this->output->writeln($line);
			return;
		}
		$this->lines[] = $line;
		if (count($this->lines) > $this->maxLines) {
			array_shift($this->lines);
		}
		$this->output->clear();
		foreach ($this->lines as $line) {
			$this->output->writeln($line);
		}
	}
}
