
<?php
use yii\helpers\Html;


?>
<div class="side-menu animate-dropdown outer-bottom-xs">
    <div class="head"><i class="icon fa fa-align-justify fa-fw"></i> Каталог</div>        
    <nav class="yamm megamenu-horizontal" role="navigation">
        <?php if (isset($result['items'])) { ?>
            <ul class="nav">       
                <?php foreach ($result['items'] as $item) { ?>
                    <?php
                    if (isset($item['items'])) {
                        $href = '#';
                        $datatoggle = 'dropdown';
                        $class = '';
                    } else {
                        $datatoggle = '';
                        $href = Yii::$app->urlManager->createUrl($item['url'][0], array('seo_alias' => $item['url']['seo_alias']));
                        $class = 'no-arrow';
                    }
                    ?>
                    <li class="dropdown menu-item">
                        <a href="<?= $href; ?>" class="dropdown-toggle <?= $class ?>" data-hover="<?= $datatoggle ?>" data-toggle="<?= $datatoggle ?>"><?= $item['label'] ?></a>
                        <?php if (isset($item['items'])) { ?>
                            <ul class="dropdown-menu mega-menu">
                                <li class="yamm-content">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12">
                                            <ul class="links list-unstyled">
                                                <?php
                                                $i = 0;
                                                foreach ($item['items'] as $subitem) {
                                                    $i++;

                                                    echo Html::tag('li', Html::a($subitem['label'], $subitem['url']));
                                                    echo $subitem['total_count'];
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </li>               
                            </ul>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </nav>
</div>

