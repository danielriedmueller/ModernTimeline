<?php

declare( strict_types = 1 );

namespace ModernTimeline;

use ModernTimeline\ResultFacade\Subject;
use ModernTimeline\SlidePresenter\SlidePresenter;
use SMWDITime;

class JsonBuilder {

	private $slidePresenter;
    private $headlinePresenter;

    public function __construct( SlidePresenter $slidePresenter, HeadlinePresenter $headlinePresenter ) {
		$this->slidePresenter = $slidePresenter;
		$this->headlinePresenter = $headlinePresenter;
	}

	/**
	 * @param Event[] $events
	 * @return array
	 */
	public function eventsToTimelineJson( array $events ): array {
		$jsonEvents = [];

		foreach ( $events as $event ) {
			$jsonEvents[] = $this->buildEvent( $event );
		}

		return [ 'events' => $jsonEvents ];
	}

	public function buildEvent( Event $event ): array {
		$jsonEvent = [
			'text' => [
				'headline' => $this->headlinePresenter->getText( $event->getSubject() ),
				'text' =>  $this->slidePresenter->getText( $event->getSubject() )
			],
			'start_date' => $this->timeToJson( $event->getStartDate() ),
		];

		if ( $event->getEndDate() !== null ) {
			$jsonEvent['end_date'] = $this->timeToJson( $event->getEndDate() );
		}

		if ( $event->hasImage() ) {
			$jsonEvent['media'] = [
				'url' => $event->getImageUrl(),
				'thumbnail' => $event->getImageUrl()
			];
		}

		return $jsonEvent;
	}

	private function timeToJson( SMWDITime $time ): array {
		return [
			'year' => $time->getYear(),
			'month' => $time->getMonth(),
			'day' => $time->getDay(),
			'hour' => $time->getHour(),
			'minute' => $time->getMinute(),
			'second' => (int)$time->getSecond(),
		];
	}

}
