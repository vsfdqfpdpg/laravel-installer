<?php


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListsCommand extends Command {
	public function configure() {
		$this->setName( 'lists' )
		     ->setHelp( "List all available laravel version." )
		     ->setDescription( 'List all laravel version.' );
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$table = new Table( $output );
		$table->setHeaders( [ 'Version' ] );
		$table->addRows( [
			[ '5.8.3' ],
			[ '5.7.28' ],
			[ '5.6.33' ],
			[ '5.5.28' ],
			[ '5.4.30' ],
			[ '5.3.0' ],
			[ '5.2.31' ],
			[ '5.1.33' ],
			[ '5.0.22' ],
			[ '4.2.11' ],
			[ '4.1.27' ]
		] );
		$table->render();
	}

}