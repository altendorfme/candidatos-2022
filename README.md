# Candidatos Eleições 2022

Dados abertos dos candidatos as Eleicões de 2022 conforme divulgado pelo TSE
- https://dadosabertos.tse.jus.br/dataset/candidatos-2022

## Estrutura de arquivos:

- **2022/csv**: CVS's originais do TSE renomeados para cada estado, além de BR para federação
- **2022/json**: A conversão direta dos CSV's sem nenhum tratamento, inclusive o TSE usa o valor #NULO# para campos vazios.
- **2022/images**: A imagem de cada candidato renomeado para o padrão de SQ_CANDIDATO
- **2022/ultima_atualizacao**: É data de ultima atualização no TSE

## Crawlers:

- **json.php**: Baixa todos os CSV's e faz a conversão para JSON
- **imagens.php**: Baixa todas as imagens dos candidatos

## Github:

- https://raw.githubusercontent.com/altendorfme/candidatos-2022/main/csv/sp.csv (uf + br)
- https://raw.githubusercontent.com/altendorfme/candidatos-2022/main/json/sp.json (uf + br)
- https://raw.githubusercontent.com/altendorfme/candidatos-2022/main/imagens/280001607829.jpg (SQ_CANDIDATO)

## jsDelivr (Cache de 1 semana):

- https://cdn.jsdelivr.net/gh/altendorfme/candidatos-2022/2022/csv/sp.csv (uf + br)
- https://cdn.jsdelivr.net/gh/altendorfme/candidatos-2022/2022/json/sp.json (uf + br)
- https://cdn.jsdelivr.net/gh/altendorfme/candidatos-2022/2022/imagens/280001607829.jpg (SQ_CANDIDATO)

## Informações adicionais:

Os dados serão processados semanalmente, como o TSE bloqueia o acesso para IPs de fora do Brasil não foi possivel automatizar esse processo via Github Actions e tambem não irei tentar utilizar um proxy para contornar.

O TSE fornece diversas rotas que trazem essas mesmas informações em tempo real, inclusive é utilizado no **imagens.php** para pegar a foto do candidato. Você pode consultar mais informações e documentações em https://dadosabertos.tse.jus.br/ ou https://divulgacandcontas.tse.jus.br.

Os limites de consulta no TSE são muito altos, mas ficam lentos e podem cair principalmente no dia da eleição. Além do bloqueio para IPs de fora do Brasil.