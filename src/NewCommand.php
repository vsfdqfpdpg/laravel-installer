<?php

use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends Command {
	protected $client;

	public function __construct( ClientInterface $client ) {
		$this->client = $client;
		parent::__construct();
	}

	public function configure() {
		$this->setName( "new" )
		     ->setDescription( "Create a new laravel application." )
		     ->setHelp( "Create a new laravel application." )
		     ->addArgument( "directory", InputArgument::REQUIRED, "The project directory." )
		     ->addArgument( "version", InputArgument::OPTIONAL, "Provide a laravel version.", "latest" );
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$version   = $input->getArgument( 'version' );
		$directory = $input->getArgument( 'directory' );

		$path = getcwd() . DIRECTORY_SEPARATOR . $directory;

		$this->checkApplicationIfExist( $path, $output );

		$this->download( $version, $filename = $this->makeFilename(), $output )
		     ->extract( $filename, $path, $output )
		     ->cleanUp( $filename );
		$output->writeln( "<info>Laravel version $version installed successfully.</info>" );

		if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
			shell_exec( 'start ' . $path );
		}
	}

	private function checkApplicationIfExist( $path, OutputInterface $output ) {
		if ( is_dir( $path ) ) {
			$output->writeln( "<error>Application already exist.</error>" );
			exit( 1 );
		}
	}

	/**
	 * @param $version
	 * @param $param
	 * @param OutputInterface $output
	 * Download laravel package.
	 *
	 * @return $this
	 * @throws Exception
	 */
	private function download( $version, $param, OutputInterface $output ) {
		$output->writeln( "<info>Begin to download laravel version: $version</info>" );

		$url = $this->getDownloadLaravelUrl( $version, $output );

		$response   = $this->client->get( $url );
		$statusCode = $response->getStatusCode();
		$output->writeln( "<info>Response status: $statusCode</info>" );

		if ( $statusCode == 200 ) {
			file_put_contents( $param, $response->getBody() );
		} else {
			$output->writeln( "<error>Response status: $statusCode</error>" );
			exit( 1 );
		}

		return $this;
	}

	/**
	 * Generate a temp filename.
	 * @return string
	 */
	private function makeFilename() {
		return getcwd() . '/laravel_' . md5( getcwd() . time() . uniqid() ) . '.zip';
	}

	/**
	 * @param $filename
	 * @param $path
	 * @param OutputInterface $output
	 *
	 * @return $this
	 */
	private function extract( $filename, $path, OutputInterface $output ) {
		$output->writeln( "<info>Begin to extract file to $path</info>" );
		$archive = new ZipArchive();
		$archive->open( $filename );
		$archive->extractTo( $path );
		$archive->close();

		$dir = array_values( array_diff( scandir( $path ), [ '.', '..' ] ) );
		if ( count( $dir ) == 1 ) {
			$temp_dir = dirname( $path ) . '/temp_dir_laravel.' . md5( time() . uniqid() );
			rename( $path . '/' . $dir[0], $temp_dir );
			rmdir( $path );
			rename( $temp_dir, $path );
		}

		return $this;
	}

	/**
	 * Delete downloaded file.
	 *
	 * @param $filename
	 */
	private function cleanUp( $filename ) {
		unlink( $filename );
	}

	/**
	 * @param $version
	 * @param OutputInterface $output
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getDownloadLaravelUrl( $version, OutputInterface $output ) {
		if ( $version == 'latest' ) {
			$url = 'http://cabinet.laravel.com/latest.zip';
		} elseif ( preg_match( '/\d/', $version ) ) {
			$res = preg_match_all( '/(\d)\.?(\d)\.?(\d{1,2})/', $version, $matches );
			if ( $res ) {
				$version = 'v' . $matches[1][0] . '.' . $matches[2][0] . '.' . $matches[3][0];
				$url     = 'https://github.com/laravel/laravel/archive/' . $version . '.zip';
			} else {
				$this->suggestVersion( $version, $output );
				exit( 1 );
			}
		} else {
			$this->suggestVersion( $version, $output );
			exit( 1 );

		}

		return $url;
	}

	/**
	 * @param $version
	 * @param OutputInterface $output
	 *
	 * @throws Exception
	 */
	private function suggestVersion( $version, OutputInterface $output ) {
		$output->writeln( "<error>Version: $version is not correct.</error>" );
		$output->writeln( "<info>You can try those version below.</info>" );
		$command    = $this->getApplication()->find( 'lists' );
		$arguments  = [
			'command' => 'lists',
		];
		$greetInput = new ArrayInput( $arguments );
		$command->run( $greetInput, $output );
	}
}