<?php
class OffersHandler implements HandlerInterface
{
    public function prepare($offer)
    {
        $categories = $this->GetCategories();

        foreach ($offer as $c => $k) {
            $offer[$c]['url'] = 'http://local.s3.prefest.ru/'.$categories[$k['categoryId']]['path'].'/'.$k['url'];
            if(isset($k['picture'])){
                $file = 'http://local.s3.prefest.ru/images/detailed/1/'.$k['picture'];
                $file_headers = @get_headers($file);
                if($file_headers[0] == 'HTTP/1.1 200 OK') {
                    $offer[$c]['picture'] = $file;
                }
                else {
                    $file = 'http://local.s3.prefest.ru/images/detailed/0/'.$k['picture'];
                    $file_headers = @get_headers($file);
                    if($file_headers[0] == 'HTTP/1.1 200 OK') {
                        $offer[$c]['picture'] = $file;
                    }
                    else
                        unset($offer[$c]['picture']);
                }
            }
            if(isset($k['article'])){
                $offer[$c]['params'][] = array(
                    'name' => 'Артикул',
                    'code' => 'article',
                    'value' => $k['article']
                );
            }
        }
        return $offer;
    }

    public function GetCategories(){
        $builder = new ExtendedOffersBuilder();
        $data = $builder->buildCategories();

        $categories = array();

        foreach($data as $category) {
            $categories[$category['id']] = array(
                'parentId' => $category['parentId'],
                'path' => $category["path"],
                'name' => $category['name']
            );
        }
        foreach($categories as $k => $v){
            if ($categories[$k]['parentId'] != 0){
                $ind = $categories[$k]['parentId'];
                $path = $categories[$k]['path'];
                while(true){
                    if ($categories[$ind]['parentId'] != 0){
                        $path = $categories[$ind]['path'].'/'.$path;
                        $ind = $categories[$ind]['parentId'];
                    }
                    elseif ($categories[$ind]['parentId'] == 0){
                        $path = $categories[$ind]['path'].'/'.$path;
                        break;
                    }
                }
                $categories[$k]['path'] = $path;
            }
        }
        return $categories;
    }
}