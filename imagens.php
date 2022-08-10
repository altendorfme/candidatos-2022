<?php
require_once(dirname(__FILE__) . '/padroes.php');
require_once(dirname(__FILE__) . '/vendor/autoload.php');

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

if (PHP_SAPI !== 'cli') {
	http_response_code(401);
	exit;
}

if (is_dir(ANO) === false) {
	mkdir(ANO);
}
if (is_dir(ANO . '/imagens') === false) {
	mkdir(ANO . '/imagens');
}

foreach (ESTADOS as $uf => $state) {
	echo 'UF: ' . strtoupper($uf) . PHP_EOL;

	$candidatos = json_decode(file_get_contents(ANO . '/json/' . $uf . '.json'));
	foreach($candidatos as $candidato) {
		$codigo_candidato = $candidato->SQ_CANDIDATO;

		echo $codigo_candidato . PHP_EOL;

		$nome_arquivo = ANO . '/imagens/' . $codigo_candidato . '.jpg';
		if(file_exists($nome_arquivo)) {
			echo '- Arquivo encontrado!' . PHP_EOL;
		} else {
			echo '- Arquivo não encontrado!' . PHP_EOL;
			$stream_context_create = array(
				"ssl" => array(
					"verify_peer" => false,
					"verify_peer_name" => false,
				),
			);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://divulgacandcontas.tse.jus.br/divulga/rest/v1/candidatura/buscar/2022/' . strtoupper($uf) . '/2040602022/candidato/' . $codigo_candidato,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPHEADER => array(
					'cache-control: no-cache',
					'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36'
				),
			));
			$divulga_candcontas = curl_exec($curl);
			curl_close($curl);

			if ($divulga_candcontas === FALSE) {
				http_response_code(404);
			} else {
				$dados = json_decode($divulga_candcontas);

				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => $dados->fotoUrl,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'GET',
					CURLOPT_HTTPHEADER => array(
						'cache-control: no-cache',
						'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36'
					),
				));
				$image = curl_exec($curl);
				curl_close($curl);

				file_put_contents($nome_arquivo, $image);

				if(filesize($nome_arquivo) < 3000) {
					unlink($nome_arquivo);
				}
			}
		}
	}
}