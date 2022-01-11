<?php

require_once('php/db.php');
require_once('php/loadArticles.php');
require_once('php/error_management.php');

session_start();
use DB\DBAccess;

// prendere il risultato dal DB
$db = new DBAccess();

$connection = $db->openDBConnection();
$user_output = "";
$carousel="";
$slides = array();

$articlesToLoad = 5;
$lastArticleLoaded = 0;

if($connection){
    $nArticles=10;

    $articles = $db->getTopArticles($nArticles);
    if($articles=="ErroreDB"){ 
        $db->closeDBConnection();
        $user_output = createEmptyDBErrorHTML("articles");
    }
    else{
        $nArticles = count($articles);                       //anche se il LIMIT della query è nArticles potrebbero essercene di meno nel db
        $tags = $db->getTopArticleTags($nArticles);
        $MostLiked = $db->getMostLikedArticles();
        $HotGames = $db->getHotGames();
        if($MostLiked)
            $CarouselTags = $db->getCarouselTags($MostLiked);
        $db->closeDBConnection();   //ho finito di usare il db quindi chiudo la connessione
        if($articles){
            $user_output = loadArticles($articles, $tags, 0, $nArticles);
            $lastArticleLoaded = $nArticles;
        }
        if($MostLiked){
            foreach($MostLiked as $art){
                $HTMLSlide="";
                $HTMLSlide = 
                    '<a class="card-article-link" href="article.php?id='.$art['id'].'">
                    <article>
                        <div class="card-article-image">
                            <img src="images/article_covers/'.$art['cover_img'].'"/>
                        </div>
                        <div class="card-article-info">
                            <h3>'.$art['title'].'</h3>
                            <h4>'.$art['subtitle'].'</h4>
                            <p>'.$art['publication_date'].'</p>';
                if($CarouselTags[$art['id']]){
                    $intro=true;
                    foreach($CarouselTags[$art['id']] as $tag){
                        if($intro){
                            $HTMLSlide .= '<ul id="card-article-tags" class="tag-list">';
                            $intro=false;
                        }
                        $HTMLSlide .= '<li class="tag">'.$tag['name'].'</li>';
                    }
                    if(!$intro)
                        $HTMLSlide .= '</ul>';
                }
                $HTMLSlide .= '</div>
                                    </article>
                                    </a>';
                array_push($slides, $HTMLSlide);
            }
        }
        if(isset($HotGames) && $HotGames != "WrongQuery") {
            $HotGamesHTML = '
            </div>
            <div id="hot-games">
                <h1 class="subtitle">Hot Games</h1>
                <ul class="game-list" id="game-list">';
            for($i = 0; $i < count($HotGames); $i++){
                $game = $HotGames[$i];            
                $HotGamesHTML .= '<li class="card" id="'.$game['name'].'">
                <a href="search.php?game='.urlencode($game['name']).'"><img src="/images/games/' . $game['img'] . '" alt="' . $game['name'] . ' cover" class="card-img"></a>
                <div class="card-content">
                    <h2 class="card-title"><a href="search.php?game='.urlencode($game['name']).'" class="card-title-link">' . $game['name'] . '</a></h2>';
                    $HotGamesHTML .= '</div>
                            </li>';
            }
            $HotGamesHTML .= '</ul></div>';
        }
        //str_replace per il carousel
        if(count($slides)>0){
            $carousel=' 
                <div class="slider">
                    <h1 class="subtitle">Most liked</h1>
                        <div class="slides">';
            $i=1;
            foreach($slides as $art){
                if($art!=""){
                    $carousel .= '<div id="slide-'.$i.'">'.$art.'</div>';
                    $i++;
                }
            }
            $carousel .= $HotGamesHTML . '</div>';
        }
        $user_output.='<button class="action-button" id="more-articles" type="button" onClick=loadMore('.$lastArticleLoaded.')>Load more</button></div>';
    }      
} else {
    $user_output = createDBErrorHTML();
}

$htmlPage = file_get_contents("html/home.html");

//header footer and dynamic navbar all at once (^^^ sostituisce il commento qua sopra ^^^)
require_once('php/full_sec_loader.php');

$htmlPage = str_replace("<carousel/>", $carousel, $htmlPage);
$htmlPage = str_replace("<AllArticles/>", $user_output, $htmlPage);

echo $htmlPage;

?>