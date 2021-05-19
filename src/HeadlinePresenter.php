<?php

declare( strict_types = 1 );

namespace ModernTimeline;

use ModernTimeline\ResultFacade\Subject;
use SMW\DataValueFactory;

class HeadlinePresenter {
    private const PARAM_HEADLINEPROP = 'headline property';

    private $parameters;

    public function __construct( array $parameters ) {
        $this->parameters = $parameters;
    }

    /**
     * If headline property is present, use value from that property.
     * Otherwise return DISPLAYTITLE or WikiPage Title
     *
     * @param Subject $subject
     * @return string
     */
    public function getText( Subject $subject ): string {
        if ($this->parameters[self::PARAM_HEADLINEPROP]) {
            $value = $this->getHeadlinePropertyValue($subject, $this->parameters[self::PARAM_HEADLINEPROP]);
            if ($value) {
                return $value;
            }
        }

        return $this->newHeadline($subject->getWikiPage()->getTitle());
    }

    /**
     * Use DISPLAYTITLE magic word, if defined.
     *
     * @param \Title $title
     * @return string
     */
    private function newHeadline( \Title $title ): string {
        $dbr = wfGetDB( DB_REPLICA );
        $displayTitle = $dbr->selectField(
            'page_props',
            'pp_value',
            array( 'pp_propname' => 'displaytitle', 'pp_page' => $title->getArticleId() ),
            __METHOD__
        );

        return $displayTitle ? $displayTitle : \Html::element(
            'a',
            [ 'href' => $title->getFullURL() ],
            $title->getText()
        );
    }

    private function getHeadlinePropertyValue( Subject $subject, $property ) {
        foreach ( $subject->getPropertyValueCollections() as $propertyValues ) {
            if ($propertyValues->getPrintRequest()->getData()->getInceptiveProperty()->getKey() === $property) {
                foreach ( $propertyValues->getDataItems() as $dataItem ) {
                        return $this->dataItemToText( $dataItem );
                }
            }
        }

        return null;
    }

    private function dataItemToText( \SMWDataItem $dataItem ): string {
        return DataValueFactory::getInstance()->newDataValueByItem( $dataItem )->getLongHTMLText();
    }
}
