<?

function products_as_select($row_id){

        global $db;
        if (!$row_id){
                $result_set=str_replace("CODE:","",$list_code);
                $sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id ORDER BY artists.artist,products.title";
                $result=$db->query($sql);
                $code_results=array();
                while ($code_res=$db->fetch_array($result)){
                        array_push($code_results,$code_res['id'] . ";;". $code_res['artist'] . " - " . $code_res['title']);
                }
                $return_set=join(",",$code_results);
                return $return_set;

        } else {

                $sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id WHERE products.id = $row_id ORDER BY artists.artist,products.title";
                $code_res=$db->query($sql);
                while ($cod_arr=$db->fetch_array($code_res)){
                        $rv=$cod_arr['artist'] . " - " . $cod_arr['title'];
                }
                return $rv;
        }
}

?>
