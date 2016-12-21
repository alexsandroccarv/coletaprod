<!DOCTYPE html>
<?php
    include('inc/config.php'); 
    include('inc/functions.php');

    $result_get = analisa_get($_GET);
    $query_complete = $result_get['query_complete'];
    $query_aggregate = $result_get['query_aggregate'];
    //$escaped_url = $result_get['escaped_url'];
    $limit = $result_get['limit'];
    $page = $result_get['page'];
    //$new_get = $result_get['new_get'];
    $skip = $result_get['skip'];

    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'size'=> $limit,
        'from' => $skip,   
        'body' => $query_complete
    ];  
    
    $cursor = $client->search($params);    

    $total = $cursor["hits"]["total"];

?>
<html>
    <head>
        <?php
            include('inc/meta-header.php'); 
        ?>        
        <title>Lattes USP - Resultado da busca</title>
        <script src="inc/uikit/js/components/accordion.min.js"></script>
        <script src="inc/uikit/js/components/pagination.min.js"></script>
        <script src="inc/uikit/js/components/datepicker.min.js"></script>
        <script src="inc/uikit/js/components/tooltip.min.js"></script>
        
    </head>
    <body>
        <?php include('inc/navbar.php'); ?>        
        
        <div class="uk-container uk-container-center">
            <div class="uk-grid" data-uk-grid>                        
                <div class="uk-width-small-1-2 uk-width-medium-2-6">                    
                    

<div class="uk-panel uk-panel-box">
    <form class="uk-form" method="get" action="result_trabalhos.php">
    <fieldset>
        
        <?php if (!empty($_GET["search"])) : ?>
        <legend>Filtros ativos</legend>
            <div class="uk-form-row">
                <?php foreach($_GET["search"] as $filters): ?>
                    <input type="checkbox" name="search[]" value="<?php print_r(str_replace('"','&quot;',$filters)); ?>" checked><?php print_r($filters); ?><br/>
                <?php endforeach; ?>
            </div>
        <div class="uk-form-row"><button type="submit" class="uk-button-primary">Retirar filtros</button></div>
        <?php endif;?> 
    </fieldset>        
    </form>    
    <hr>
    <h3 class="uk-panel-title">Refinar meus resultados</h3>    
    <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
        <hr>
    <?php
        $facets = new facets();
        $facets->query_aggregate = $query_aggregate;
        
        $facets->facet("natureza",10,"Natureza",null);
        $facets->facet("tipo",10,"Tipo de material",null);
        
        $facets->facet("autores.nome_completo_do_autor",100,"Nome completo do autor",null);
        $facets->facet("autores.nro_id_cnpq",100,"Número do lattes",null);
        $facets->facet("id_usp",100,"Número USP",null);
        
        
        $facets->facet("pais",200,"País de publicação",null);
        $facets->facet("ano",120,"Ano de publicação","desc");
        $facets->facet("idioma",40,"Idioma",null);
        $facets->facet("meio_de_divulgacao",100,"Meio de divulgação",null);
        $facets->facet("palavras_chave",100,"Palavras-chave",null);
        
        $facets->facet("area_do_conhecimento.nome_grande_area_do_conhecimento",100,"Nome da Grande Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nome_da_area_do_conhecimento",100,"Nome da Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nome_da_sub_area_do_conhecimento",100,"Nome da Sub Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nome_da_especialidade",100,"Nome da Especialidade",null);
        
        
        $facets->facet("evento.classificacao_do_evento",100,"Classificação do evento",null); 
        $facets->facet("evento.nome_do_evento",100,"Nome do evento",null);
        $facets->facet("evento.cidade_do_evento",100,"Cidade do evento",null);
        $facets->facet("evento.ano_de_realizacao_do_evento",100,"Ano de realização do evento",null);
        $facets->facet("evento.titulo_dos_anais",100,"Título dos anais",null);
        $facets->facet("evento.volume_dos_anais",100,"Volume dos anais",null);
        $facets->facet("evento.fasciculo_dos_anais",100,"Fascículo dos anais",null);
        $facets->facet("evento.serie_dos_anais",100,"Série dos anais",null);
        $facets->facet("evento.isbn",100,"ISBN dos anais",null);
        $facets->facet("evento.nome_da_editora",100,"Editora dos anais",null);
        $facets->facet("evento.cidade_da_editora",100,"Cidade da editora",null);
        $facets->facet("evento.nome_do_evento_ingles",100,"Nome do evento em inglês",null);
        
    ?>
    </ul>
        <?php if(!empty($_SESSION['oauthuserdata'])): ?>
            <h3 class="uk-panel-title uk-margin-top">Informações administrativas</h3>
            <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
            <hr>
            <?php         

            ?>
            </ul>
        <?php endif; ?>
    <hr>
    <form class="uk-form">
    <fieldset>
        <legend>Limitar datas</legend>

        <script>
            $( function() {
            $( "#limitar-data" ).slider({
              range: true,
              min: 1900,
              max: 2030,
              values: [ 1900, 2030 ],
              slide: function( event, ui ) {
                $( "#date" ).val( "ano:[" + ui.values[ 0 ] + " TO " + ui.values[ 1 ] + "]" );
              }
            });
            $( "#date" ).val( "ano:[" + $( "#limitar-data" ).slider( "values", 0 ) +
              " TO " + $( "#limitar-data" ).slider( "values", 1 ) + "]");
            } );
        </script>
        <p>
          <label for="date">Selecionar período de tempo:</label>
          <input type="text" id="date" readonly style="border:0; color:#f6931f; font-weight:bold;" name="search[]">
        </p>        
        <div id="limitar-data" class="uk-margin-bottom"></div>        
        <?php if(!empty($_GET["search"])): ?>
            <?php foreach($_GET["search"] as $search_expression): ?>
                <input type="hidden" name="search[]" value="<?php echo str_replace('"','&quot;',$search_expression); ?>">
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="uk-form-row"><button class="uk-button-primary">Limitar datas</button></div>
    </fieldset>        
    </form>
    <hr>
    <?php if(!empty($_SESSION['oauthuserdata'])): ?>
            <fieldset>
                <legend>Gerar relatório</legend>                  
                <div class="uk-form-row"><a href="<?php echo 'http://'.$_SERVER["SERVER_NAME"].'/~bdpi/report.php?'.$_SERVER["QUERY_STRING"].''; ?>" class="uk-button-primary">Gerar relatório</a>
                </div>
            </fieldset>        
    <?php endif; ?>                
            
</div>
    
                    
                </div>
                <div class="uk-width-small-1-2 uk-width-medium-4-6">
                    
        
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-1-3"> 
                            
                        </div>
                        <div class="uk-width-1-3"><p class="uk-text-center"><?php print_r(number_format($total,0,',','.'));?> registros</p></div>
                        <div class="uk-width-1-3">
                            <ul class="uk-pagination" data-uk-pagination="{items:<?php print_r($total);?>,itemsOnPage:<?php print_r($limit);?>,displayedPages:3,edges:1,currentPage:<?php print_r($page-1);?>}"></ul>                         
                        </div>
                    </div>
                    
                    <hr class="uk-grid-divider">
                    <div class="uk-width-1-1 uk-margin-top uk-description-list-line">
                    <ul class="uk-list uk-list-line">   
                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                    
                        <li>                        
                            <div class="uk-grid uk-flex-middle" data-uk-grid-   margin="">
                                <div class="uk-width-medium-2-10 uk-row-first">
                                    <div class="uk-panel uk-h6 uk-text-break">
                                        <a href="result_trabalhos.php?type[]=<?php echo $r["_source"]['tipo'];?>"><?php echo ucfirst(strtolower($r["_source"]['tipo']));?></a>
                                    </div>
                                    
                                </div>
                                <div class="uk-width-medium-8-10 uk-flex-middle">
                                    
                                    <ul class="uk-list">
                                        <li class="uk-margin-top uk-h4">
                                            <strong><a href="single.php?_id=<?php echo  $r['_id'];?>"><?php echo $r["_source"]['titulo'];?> (<?php echo $r["_source"]['ano']; ?>)</a></strong>
                                        </li>
                                        <li class="uk-h6">
                                            Autores:
                                            <?php if (!empty($r["_source"]['autores'])) : ?>
                                            <?php foreach ($r["_source"]['autores'] as $autores) {
                                                $authors_array[]='<a href="result_trabalhos.php?search[]=autores.nome_completo_do_autor.keyword:&quot;'.$autores["nome_completo_do_autor"].'&quot;">'.$autores["nome_completo_do_autor"].'</a>';
                                            } 
                                           $array_aut = implode(", ",$authors_array);
                                            unset($authors_array);
                                            print_r($array_aut);
                                            ?>
                                            
                                           
                                            <?php endif; ?>                           
                                        </li>
                                        
                                        <?php if (!empty($r["_source"]['ispartof'])) : ?><li class="uk-h6">In: <a href="result_trabalhos.php?search[]=ispartof.keyword:&quot;<?php echo $r["_source"]['ispartof'];?>&quot;"><?php echo $r["_source"]['ispartof'];?></a></li><?php endif; ?>
                                        
                                        <li class="uk-h6">
                                            Unidades USP:
                                            <?php if (!empty($r["_source"]['unidadeUSP'])) : ?>
                                            <?php $unique =  array_unique($r["_source"]['unidadeUSP']); ?>
                                            <?php foreach ($unique as $unidadeUSP) : ?>
                                                <a href="result_trabalhos.php?search[]=unidadeUSP.keyword:&quot;<?php echo $unidadeUSP;?>&quot;"><?php echo $unidadeUSP;?></a>
                                            <?php endforeach;?>
                                            <?php endif; ?>
                                        </li>
                                        
                                        <li class="uk-h6">
                                            Assuntos:
                                            <?php if (!empty($r["_source"]['palavras_chave'])) : ?>
                                            <?php foreach ($r["_source"]['palavras_chave'] as $assunto) : ?>
                                                <a href="result_trabalhos.php?search[]=palavras_chave.keyword:&quot;<?php echo $assunto;?>&quot;"><?php echo $assunto;?></a>
                                            <?php endforeach;?>
                                            <?php endif; ?>
                                        </li>
                                        <?php if (!empty($r["_source"]['fatorimpacto'])) : ?>
                                        <li class="uk-h6">
                                            <p>Fator de impacto da publicação: <?php echo $r["_source"]['fatorimpacto'][0]; ?></p>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    <?php endforeach;?>
                    </ul>
                    </div>
                    <hr class="uk-grid-divider">
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-1-2"><p class="uk-text-center"><?php print_r($total);?> registros</p></div>
                        <div class="uk-width-1-2">
                            <ul class="uk-pagination" data-uk-pagination="{items:<?php print_r($total);?>,itemsOnPage:<?php print_r($limit);?>,displayedPages:3,edges:1,currentPage:<?php print_r($page-1);?>}"></ul>                         
                        </div>
                    </div>                   
                    

                    
                </div>
            </div>
            <hr class="uk-grid-divider">
<?php include('inc/footer.php'); ?>          
        </div>
                


        <script>
        $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
            var url = window.location.href.split('&page')[0];
            window.location=url +'&page='+ (pageIndex+1);
        });
        </script>    

<?php include('inc/offcanvas.php'); ?>         
        
    </body>
</html>