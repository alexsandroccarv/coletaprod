<?php

function store_record ($client,$sha256,$query){
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'id' => "$sha256",
        'body' => $query
    ];
    $response = $client->update($params);
    echo ''.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function store_curriculo ($client,$id_lattes,$query){
    
    $params = [
        'index' => 'lattes',
        'type' => 'curriculo',
        'id' => "$id_lattes",
        'body' => $query
    ];
    $response = $client->update($params);
    echo ''.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function compararRegistrosLattes ($client,$query_year,$query_title,$query_nome_do_evento,$query_tipo) {
 
    $query = '
    {
        "min_score": 30,
        "query":{
            "bool": {
                "should": [
                    {
                        "multi_match" : {
                            "query":      "'.$query_tipo.'",
                            "type":       "cross_fields",
                            "fields":     [ "tipo" ],
                            "minimum_should_match": "100%" 
                         }
                    },		
                    {
                        "multi_match" : {
                            "query":      "'.$query_title.'",
                            "type":       "cross_fields",
                            "fields":     [ "titulo" ],
                            "minimum_should_match": "90%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_nome_do_evento.'",
                            "type":       "cross_fields",
                            "fields":     [ "evento.nome_do_evento" ],
                            "minimum_should_match": "80%" 
                         }
                    },		    
                    {
                        "multi_match" : {
                            "query":      "'.$query_year.'",
                            "type":       "best_fields",
                            "fields":     [ "ano" ],
                            "minimum_should_match": "75%" 
                        }
                    }
                ],
                "minimum_should_match" : 1               
            }
        }
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function compararRegistrosLattesArtigos($client,$query_year,$query_title,$query_titulo_do_periodico,$query_doi,$query_tipo) {
 
    $query = '
    {
        "min_score": 0,
        "query":{
            "bool": {
                "should": [
                    {
                        "multi_match" : {
                            "query":      "'.$query_tipo.'",
                            "type":       "cross_fields",
                            "fields":     [ "tipo" ],
                            "minimum_should_match": "100%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_doi.'",
                            "type":       "cross_fields",
                            "fields":     [ "doi" ],
                            "minimum_should_match": "100%" 
                         }
                    },			    		
                    {
                        "multi_match" : {
                            "query":      "'.$query_title.'",
                            "type":       "cross_fields",
                            "fields":     [ "titulo" ],
                            "minimum_should_match": "90%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_titulo_do_periodico.'",
                            "type":       "cross_fields",
                            "fields":     [ "periodico.titulo_do_periodico" ],
                            "minimum_should_match": "80%" 
                         }
                    },		    
                    {
                        "multi_match" : {
                            "query":      "'.$query_year.'",
                            "type":       "best_fields",
                            "fields":     [ "ano" ],
                            "minimum_should_match": "75%" 
                        }
                    }
                ],
                "minimum_should_match" : 2               
            }
        }
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function compararDoi($client,$query_doi) {
 
    $query = '
    {
        "min_score": 0,
        "query":{
		"match" : {
			"doi": "'.$query_doi.'"
		}
	}
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function analisa_get($get) {
    
    $search_fields = "";
    if (!empty($get['fields'])) {
        $search_fields = implode('","',$get['fields']);  
    } else {            
        $search_fields = "_all";
    }    
    
    if (!empty($get['search'])){
        $get['search'] = str_replace('"','\"',$get['search']);
    }
    
    /* Pagination */
    if (isset($get['page'])) {
        $page = $get['page'];
        unset($get['page']);
    } else {
        $page = 1;
    }
    
    /* Pagination variables */
    $limit = 20;
    $skip = ($page - 1) * $limit;
    $next = ($page + 1);
    $prev = ($page - 1);
    $sort = array('year' => -1);       
    
    if (!empty($get['codpes'])){        
        $get['search'][] = 'codpes:'.$get['codpes'].'';
    }
    
    if (!empty($get['assunto'])){        
        $get['search'][] = 'subject:\"'.$get['assunto'].'\"';
    }    
    
    if (!empty($get['search'])){
        $query = implode(" ", $get['search']); 
    } else {
        $query = "*";
    }
    
    $search_term = '
        "query_string" : {
            "fields" : ["'.$search_fields.'"],
            "query" : "'.$query.'",
            "default_operator": "AND",
            "analyzer":"portuguese",
            "phrase_slop":10
        }                
    ';    
    
    $query_complete = '{
        "sort" : [
                { "ano.keyword" : "desc" }
            ],    
        "query": {
        '.$search_term.'
        }
    }';
    $query_aggregate = '
        "query": {
            '.$search_term.'
        },
    ';
 
    return compact('page','get','new_get','query_complete','query_aggregate','url','escaped_url','limit','termo_consulta','data_inicio','data_fim','skip');
}

class facets {   
    
    public function facet($field,$tamanho,$field_name,$sort) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $sort_query="";
        if (!empty($sort)){
             $sort_query = '"order" : { "_term" : "'.$sort.'" },';  
        }     
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        '.$sort_query.'
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0,          
            'body' => $query
        ];
        $response = $client->search($params);    
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$field_name.'</a>';
        echo ' <ul class="uk-nav-sub">';
        //$count = 1;
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<div class="uk-grid"><div class="uk-width-4-5">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</div> <div class="uk-width-1-5"> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-plus" data-uk-tooltip title="E"></a> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=-'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-minus" data-uk-tooltip title="NÃO"></a>  <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=OR '.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-check-circle-o" data-uk-tooltip title="OU"></a></div>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo   '</ul></li>';
    }
    
    public function rebuild_facet($field,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        "order" : { "_count" : "desc" },
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';    
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0, 
            'body' => $query
        ];
        $response = $client->search($params);
        echo '<li class="uk-parent">';
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6">';        
            echo '<a href="autoridades.php?term='.$facets['key'].'">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</li>';
        };
        echo   '</ul>
          </li>';
    }
    public function facet_range($campo,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '
        {
            '.$query_aggregate.'
            "aggs" : {
                "ranges" : {
                    "range" : {
                        "field" : "metrics.'.$campo.'",
                        "ranges" : [
                            { "to" : 1 },
                            { "from" : 1, "to" : 2 },
                            { "from" : 2, "to" : 5 },
                            { "from" : 5, "to" : 10 },
                            { "from" : 10, "to" : 100 },
                            { "from" : 100 }
                        ]
                    }
                }
            }
         }
         ';
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0,          
            'body' => $query
        ];
        $response = $client->search($params); 
        //print_r($response);
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        echo '<form>';
        //$count = 1;
        foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<input type="checkbox" name="'.$campo.'[]" value="'.$facets['key'].'"><a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+metrics.'.$campo.':&quot;'.$facets['key'].'&quot;">Intervalo '.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo '<input type="hidden" checked="checked" name="operator" value="AND">';
        echo '<button type="submit" class="uk-button-primary">Limitar facetas</button>';
        echo '</form>';
        echo   '</ul></li>';    
    }
    
    
}

?>