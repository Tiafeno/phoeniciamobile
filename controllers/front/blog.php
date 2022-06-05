<?php


if (file_exists(_PS_MODULE_DIR_.'prestablog/prestablog.php')) {
    include_once(_PS_MODULE_DIR_.'prestablog/prestablog.php');
    include_once(_PS_MODULE_DIR_.'prestablog/class/news.class.php');
    include_once(_PS_MODULE_DIR_.'prestablog/class/categories.class.php');
} else {
    die("Module prestablog est non installé");
}

abstract class BlogModel {
    protected ?object $model;
}

class Blog extends BlogModel {
    public function __construct(?array $model) {
        $this->model = (object)$model;
    }

    public function __get($key) {
        return isset($this->model->{$key}) ? $this->model->{$key} : null ;
    }
}

class PhoeniciamobileBlogModuleFrontController extends ModuleFrontController {
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * It gets the latest news from the database and returns it as a JSON object
     */
    public function postProcess() {
        $news = NewsClass::getListe(
            (int)$this->context->cookie->id_lang,
            1,
            0,
            null,
            null,
            'n.`date`',
            'desc',
            null,
            Date('Y-m-d H:i:s'),
            (Tools::getValue('rss') ? (int)Tools::getValue('rss') : null),
            1,
            (int)Configuration::get('prestablog_rss_title_length'),
            (int)Configuration::get('prestablog_rss_intro_length')
        );
        $data = [];
        foreach ($news as $new) {
            $blog = new Blog($new);
            $data[] = [
                "title" => $blog->title,
                "content" => $blog->content,
                "paragraph" => $blog->paragraph,
                "categories" => $blog->categories,
                "paragraph_crop" => $blog->paragraph_crop
            ];
        }

        echo Tools::jsonEncode($data);
        die();
    }
}

