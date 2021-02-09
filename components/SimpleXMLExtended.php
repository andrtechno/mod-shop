<?php

namespace panix\mod\shop\components;

class SimpleXMLExtended extends \SimpleXMLElement
{

    public function addChildWithCDATA($name, $value = NULL, $namespace = NULL)
    {
        $new_child = $this->addChild($name, '', $namespace);

        if ($new_child !== NULL) {
            $node = dom_import_simplexml($new_child);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));
        }
        return $new_child;
    }
}