<?php 
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;
use \Doctrine\Common\Annotations\AnnotationRegistry;
use \Symfony\Component\Console\Application;
use \SixtyNine\Cloud\Builder\FiltersBuilder;
use \SixtyNine\Cloud\Builder\WordsListBuilder;
use \SixtyNine\Cloud\Builder\CloudBuilder;
use \SixtyNine\Cloud\Factory\FontsFactory;
use \SixtyNine\Cloud\Factory\PlacerFactory;
use \SixtyNine\Cloud\Renderer\CloudRenderer;
use \SixtyNine\Cloud\Command\CommandsHelper;
use \SixtyNine\DataTypes;
use \SixtyNine\Cloud\Builder\PalettesBuilder;
use \SixtyNine\Cloud\Color\RandomColorGenerator;

$curl = new Curl();
$curl->get('https://d3.ru/api/posts/1498038/comments/');
$text = '';
if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
	 $comments = $curl->response->comments;
	 foreach ($comments as &$value) {
	 	$string = strip_tags($value->body);
	 	$text .= preg_replace("/[^[:alnum:][:space:]]/u", ' ', $string);	 
	}
}
function dkeywords($keywords, $trash) 
{ 
$keywords = trim($keywords); 
$keywords = str_replace($trash, ' ', $keywords); 
return $keywords; 
} 

$remove = array ('более','менее','очень','крайне','скоре','каждый','другие','который','когда','однако','если','чтобы','хотя','смотря','как','также','так','зато','что','или','потом','эти','тогда','тоже','словно','ежели','кабы','коли','ничем','чем','без','перед','при','через','нет','над','для','под', 'про', 'все','кто','что','какой','чей','которые','когда','где','куда','как','это','уже','так','тем','они','она');
//$text = dkeywords($text, $remove);
$colorGenerator = new RandomColorGenerator(PalettesBuilder::create()->getRandomPalette());
//$cloud = new Application();
$filters = FiltersBuilder::create()
    ->setRemoveNumbers(false)
    ->setRemoveTrailing(true)
    ->setRemoveUnwanted(true)
    ->setMinLength(6)    
    ->build()
;
$list = WordsListBuilder::create()
	->setFilters($filters)    
    ->importWords($text)
    ->randomizeOrientation(30)
    ->randomizeColors($colorGenerator)    
    ->build('foobar')
;
$factory = FontsFactory::create('./vendor/fonts');

$fontSizeGenerator = new \SixtyNine\Cloud\FontSize\DimFontSizeGenerator();

/** @var \SixtyNine\Cloud\Model\Cloud $cloud */
$cloud = CloudBuilder::create($factory)
    ->setBackgroundColor('#ffffff')            // Cloud background color
    ->setDimension(1024, 768)                   // Cloud dimensions
    ->setFont('DejaVuSans.ttf')                      // TTF font filename
    ->setSizeGenerator($fontSizeGenerator)      // Optional, alternative font size generator
    ->setFontSizes(14, 64)                      // Minimal and maximal font size to use in the generator
    ->setPlacer(PlacerFactory::PLACER_CIRCULAR) // How the words will be placed in the cloud (Circular, Wordle, Spirangle, Linear Horizontal, Linear Vertical, Lissajou)
    ->useList($list)                            // Use the words in the given $list
    ->build()
;
$renderer = new CloudRenderer($cloud, $factory);
$renderer->renderCloud();

        
/** @var \Imagine\Gd\Image $image */
$image = $renderer->getImage();
$image->save('/tmp/image.png');