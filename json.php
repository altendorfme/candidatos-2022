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
if (is_dir(ANO . '/json') === false) {
	mkdir(ANO . '/json');
}
if (is_dir(ANO . '/csv') === false) {
	mkdir(ANO . '/csv');
}

$stream_context_create = array(
	"ssl" => array(
		"verify_peer" => false,
		"verify_peer_name" => false,
	),
);
$dados_abertos = file_get_contents('https://dadosabertos.tse.jus.br/dataset/candidatos-' . ANO, false, stream_context_create($stream_context_create));
if ($dados_abertos === FALSE) {
	http_response_code(404);
} else {
	$dom = new DOMDocument();
	libxml_use_internal_errors(TRUE);
	$dom->loadHTML($dados_abertos);
	libxml_clear_errors();
	$xpath = new DOMXPath($dom);
	$verificar_atualizacao = $xpath->query('//*[@id="content"]//span[@class="automatic-local-datetime"]/@data-datetime');

	if (file_exists('tse/' . ANO . '/ultima_atualizacao')) {
		$ultima_atualizacao = file_get_contents(ANO . '/ultima_atualizacao');
	} else {
		$ultima_atualizacao = null;
	}

	if ($ultima_atualizacao === $verificar_atualizacao[0]->nodeValue) {
		echo 'Arquivos do TSE sem atualização' . PHP_EOL;
		die();
	} else {
		$ultima_atualizacao = strftime('%d de %B de %Y às %H:%M:%S', strtotime($verificar_atualizacao[0]->nodeValue));
		echo 'Arquivos do TSE atualizados: ' . $ultima_atualizacao . PHP_EOL;

		$file = 'https://cdn.tse.jus.br/estatistica/sead/odsele/consulta_cand/consulta_cand_' . ANO . '.zip';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $file);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36');
		$consulta_candidato = curl_exec($ch);
		curl_close($ch);

		if ($consulta_candidato === FALSE) {
			echo 'Erro ao baixar o ZIP' . PHP_EOL;
			die();
		} else {
			file_put_contents(ANO . '/tse.zip', $consulta_candidato);

			$zip = new ZipArchive;
			if ($zip->open(ANO . '/tse.zip') === TRUE) {
				$zip->extractTo(ANO . '/csv/');
				$zip->close();
				echo 'Arquivos extraidos' . PHP_EOL;
				unlink(ANO . '/tse.zip');
				unlink(ANO . '/csv/leiame.pdf');
				unlink(ANO . '/csv/consulta_cand_' . ANO . '_BRASIL.csv');
			} else {
				echo 'Erro ao extrair' . PHP_EOL;
				die();
			}

			foreach (ESTADOS as $uf => $state) {
				echo 'UF: ' . strtoupper($uf) . PHP_EOL;
				unlink(ANO . '/csv/'. $uf . '.csv');

				$candidatos = ANO . '/csv/consulta_cand_' . ANO . '_' . strtoupper($uf) . '.csv';
				rename($dados, ANO . '/csv/'. $uf . '.csv');
				$candidatos = ANO . '/csv/'. $uf . '.csv';
				if (file_exists($candidatos)) {
					$candidatos = mb_convert_encoding(file_get_contents($candidatos), 'UTF-8', 'US-ASCII');
					$csv = new \ParseCsv\Csv();
					$csv->auto($candidatos);

					$json = json_encode($csv->data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
					file_put_contents(ANO . '/json/' . $uf . '.json', $json);
				} else {
					echo 'Erro ao processar CSV de ' . strtoupper($uf) . PHP_EOL;
					die();
				}
			}
			file_put_contents(ANO . '/ultima_atualizacao', $verificar_atualizacao[0]->nodeValue);
		}
	}
}
