<?php
$fileName = realpath("../midasdocs/stock/Midas_b2c.txt");
if (file_exists($fileName)) {
    global $productList;
    global $productPromotionCategory;
    $productPromotionCategory = 1025;
    $contents = explode("\n", file_get_contents($fileName));
    foreach ($contents as $id => $line) {
        $line = explode("\t", $line);
        if (!empty($line[1])) {
            if ($line[1] > 2) {
                $productList[$line[0]] = $line[1];
            }
        }
    }
} else {
//  mail("ruth@expertecommerce.co.za", "MIDAS.CO.ZA", "The file is missing for stock /home/midas/midasdocs/stock/Midas_b2c.txt");
}
global $globalInStoreButton;
$globalInStoreButton = <<<EOD
<a href="/store-locator"
    title="Enquire in Store"
    class="action tocart primary">
<span>Enquire in Store</span>
</a>
EOD;
