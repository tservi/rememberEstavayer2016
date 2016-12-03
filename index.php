<?php
// démarrage et initialisation de la session
session_start();
function reset_session()
{
    $_SESSION['replay'] = 'true';
    $_SESSION[ 'positions' ] = array();
    $_SESSION[ 'last_card_turned' ] = '';
    $_SESSION[ 'return_last_2' ] = 'no';
    unset($_SESSION['images_states']);
    
}
if(!isset($_SESSION[ 'replay' ]) )
{
    reset_session();    
}

if( isset( $_SESSION[ 'replay' ] ) &&  strlen ($_SERVER[ 'QUERY_STRING' ] ) == 0  && $_SESSION[ 'return_last_2' ] == 'no' )
{
    reset_session();
}

if( isset($_REQUEST[ 'replay' ]) && $_REQUEST[ 'replay' ] == 'true' )
{
    session_destroy();
    session_start();
    //$_SESSION['replay'] = 'true';
    reset_session();
}
if( !isset($_SESSION[ 'max_x' ]) )
{
    $_SESSION[ 'max_x' ] = 4 ;
}
if( !isset($_SESSION[ 'max_y' ]) )
{    
    $_SESSION[ 'max_y' ] = 4 ;
}
if( !isset($_SESSION[ 'default_image_url' ] ) )
{
    $_SESSION[ 'default_image_url' ] = 'Estavayer2016_F.png';
}
if( !isset($_SESSION[ 'default_image_path' ] ) )
{
    $_SESSION[ 'default_image_path' ] = 'carre/';
}
if( !isset($_SESSION[ 'return_last_2' ]) )
{
    $_SESSION[ 'return_last_2' ] = 'no';
}

$tableau_length = $_SESSION[ 'max_x' ] * $_SESSION[ 'max_y' ] ;

// selection des images
if(!isset($_SESSION[ 'images_for_the_game' ]) || count( $_SESSION[ 'images_for_the_game' ] ) == 0)
{
    include ("carre/list.php");
    $total = $images;
    $images_for_the_game = array();
    $nombre_de_tour = $tableau_length / 2 ;
    for($i =0 ; $i<$nombre_de_tour ; $i++)
    {
        $randomized = rand(0, count($total) -1 );
        $images_for_the_game[] = $total[ $randomized ];
        unset($total[$randomized]);
    }
    $_SESSION[ 'images_for_the_game' ] = $images_for_the_game;
    
}

// création de la grille pour les images 
if(!isset($_SESSION[ 'images_deployed' ]))
{
    $tableau = array();
    $col = array_fill ( 0 , $_SESSION[ 'max_x' ]-1 , '' ) ;
    $tableau = array_fill ( 0 , $_SESSION[ 'max_y' ]-1 ,  $col );
    $tableau_lineaire = range(0,  $tableau_length -1  );
    foreach ( $_SESSION[ 'images_for_the_game' ] as $val)
    {
        // placer chaque image deux fois
        for($place =0 ; $place < 2 ; $place++ )
        {
            $randomized = array_rand($tableau_lineaire);
            // $randomized = $my_y * $_SESSION[ 'max_x' ] + $my_x
            $my_y = intval( $randomized / $_SESSION[ 'max_x' ] ) ;
            $my_x = $randomized - $my_y * $_SESSION[ 'max_x' ] ;
            $tableau[ $my_y ][ $my_x ] = $val ;
            $tableau_lineaire = array_diff( $tableau_lineaire , array( $randomized ) ) ;
            
        }
    }
    $_SESSION[ 'images_deployed' ] = $tableau;
}

// gestion de l'etat de l'image
if( !isset($_SESSION['images_states'] ) )
{
  $col = array_fill ( 0 , $_SESSION[ 'max_x' ] , $_SESSION[ 'default_image_url' ] ) ;
  $_SESSION['images_states'] = array_fill ( 0 , $_SESSION[ 'max_y' ] ,  $col );  
}

// retourne les deux derniers coups 
if( isset($_SESSION[ 'return_last_2' ] ) &&  $_SESSION[ 'return_last_2' ] == 'yes' )
{
    for($i = 1 ; $i < 3 ; $i++)
    {
        $index = count($_SESSION[ 'positions' ]) - $i;
        $card = $_SESSION[ 'positions' ][ $index ];
        $y = $card[0];
        $x = $card[1];
        $_SESSION['images_states'][$y][$x] = $_SESSION[ 'default_image_url' ] ;
    }
    $_SESSION[ 'return_last_2' ] = 'no';
}

// stockage des coups et changement de l'étât des images.
if( isset( $_REQUEST[ 'posx' ]) && isset( $_REQUEST[ 'posy' ]))
{
    $x = $_REQUEST[ 'posx' ];
    $y = $_REQUEST[ 'posy' ];
    $yx = array($y, $x) ;
    $_SESSION[ 'positions' ][] = $yx ;
    if( count($_SESSION[ 'positions' ]) % 2 == 1 )
    {
        $_SESSION[ 'last_card_turned' ] = $_SESSION[ 'images_deployed' ][ $y ] [ $x ] ;
    }
    else
    {
         if( $_SESSION[ 'last_card_turned' ] != $_SESSION[ 'images_deployed' ][ $y ] [ $x ] )
         {
            $_SESSION[ 'return_last_2' ] = 'yes' ;
            header('Refresh: 2; URL=' . $_SERVER[ 'SCRIPT_URI' ]  );
         }
    }
    $_SESSION['images_states'][ $y ] [ $x ] = $_SESSION[ 'default_image_path' ] . $_SESSION[ 'images_deployed' ][ $y ] [ $x ];
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Remember Estavayer 2016 - le jeu </title>
<style>
    .card {
        width:150px;
        height:150px;
        border-radius:5px;
        border: 1px solid black;
        text-align: center;
    }
    .card img {
        width:150px;
        height:150px;
        border-radius:5px;
    }
</style>
</head>
<body>

<center>

<?php
if( $_SESSION['replay'] == 'true')
{
    $_SESSION['replay'] = 'false';
?>    

    <a href="http://www.afls.ch/remember">
        <input type="image" src="estavayer2016.png" style="width:80px;"/>            
    </a>
    <hr/>
    Remember! Cliquez sur les logo ci-dessous pour trouver les doublons.
    <hr/>

<?php
}
else
{
    $the_end = TRUE;
    for($i=0; $i< count ($_SESSION['images_states']) ; $i++)
    {
        for($j = 0; $j< count( $_SESSION['images_states'][$i] ) ;$j++)
        {
            if( $_SESSION['images_states'][$i][$j] ==  $_SESSION[ 'default_image_url' ] )
            {
                $the_end=FALSE;
            }
        }
    }
    if($the_end)
    {
        echo "<h1>Bravo, c'est gagn&eacute;!</h1>";
    }
    echo "<a href='?replay=true'>Recommencer</a>";
}
?>

<?php
echo "<table>";
for ( $i=0 ; $i < $_SESSION[ 'max_y' ] ; $i++ )
{
    echo "  <tr>";
    for( $j=0 ; $j < $_SESSION[ 'max_x' ] ; $j++ )
    {
        echo "     <td>";
        echo "      <div id='$i$j' class='card'>\r\n";
        if($_SESSION[ 'return_last_2' ] != 'yes' && $_SESSION['images_states'][$i][$j] ==  $_SESSION[ 'default_image_url' ] )
            echo "      <a href='?posy=$i&posx=$j'>\r\n";
        echo "      <img src='". $_SESSION['images_states'][$i][$j] ."' border=0 />\r\n";
        if($_SESSION[ 'return_last_2' ] != 'yes' && $_SESSION['images_states'][$i][$j] ==  $_SESSION[ 'default_image_url' ] )
            echo "      </a>\r\n";
        echo "      </div>";
        echo "     </td>";
    }
    echo "  </tr>";
}
echo "</table>";
/*
echo count($_SESSION[ 'positions' ]);
echo " ";
echo count($_SESSION[ 'positions' ]) % 2 ;
echo " ";
echo "<br/>";
*/
//print_r($_SESSION);
//var_dump($_SERVER);
?>
</center>
</body>
</html>