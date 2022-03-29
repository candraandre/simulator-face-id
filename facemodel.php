<?php
function compare($a, $b)
{
	try {
		//$output = shell_exec('docker run -it -d macgyvertechnology/face-comparison-model:2 2>&1');
		$output = shell_exec('docker run -it -d macgyvertechnology/face-comparison-model:2');
		$output = preg_replace('/[^0-9a-z]/', '', $output);
		$output = trim($output);

		// write images to container
		exec('docker cp ' . $a . ' ' . $output . ':/macgyver/temp/known.jpg');
		exec('docker cp ' . $b . ' ' . $output . ':/macgyver/temp/test.jpg');

		// Run main file
		//$probability = shell_exec("docker exec -t " . $output . " /bin/bash -c 'python3 /macgyver/main'");
		$probability = shell_exec("docker exec -t " . $output . " /bin/bash -c " . '"python3 /macgyver/main"');

		// Stop the Container
		exec("docker stop " . $output);

		// Delete the Container
		exec("docker rm " . $output);

		return $probability;
	} catch (Exception $e) {
		return false;
	}
}
