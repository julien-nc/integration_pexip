<?php

/**
 * Nextcloud - Pexip
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @copyright Julien Veyssier 2023
 */

namespace OCA\Pexip\Command;

use OCA\Pexip\AppInfo\Application;
use OCA\Pexip\Db\CallMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCalls extends Command {

	public function __construct(private CallMapper $callMapper) {
		parent::__construct();
	}

	protected function configure() {
		$this->setName('pexip:cleanup')
			->setDescription('Cleanup calls')
			->addArgument(
				'max_age',
				InputArgument::OPTIONAL,
				'The max idle time (in seconds)',
				Application::MAX_CALL_IDLE_TIME
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$maxAge = $input->getArgument('max_age');
		$cleanedUp = $this->callMapper->cleanupCalls($maxAge);
		$output->writeln('Deleted ' . $cleanedUp . ' idle calls');
		return 0;
	}
}
