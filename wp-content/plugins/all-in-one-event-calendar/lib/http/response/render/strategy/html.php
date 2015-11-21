<?php
/**
 * Render the request as html.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Http.Response.Render.Strategy
 */
class Ai1ec_Render_Strategy_Html extends Ai1ec_Http_Response_Render_Strategy {

	/**
	 * Twig page content placeholder.
	 */
	const CALENDAR_PLACEHOLDER = '<!-- AI1EC_PAGE_CONTENT_PLACEHOLDER -->';

	/**
	 * @var string the event html.
	 */
	protected $_html;

	/**
	 * @var string The html for the footer of the event.
	 */
	protected $_html_footer = '';

	/**
	 * Caller identifier. Just for paranoid check in append_content method.
	 * Expected 'calendar' or none.
	 *
	 * @var string
	 */
	protected $_caller     = '';

	/**
	 * Registers proper filters for content modifications.
	 *
	 * @param array $params Function params.
	 *
	 * @return void Method does not return.
	 */
	public function render( array $params ) {
		$this->_html = $params['data'];
		if ( isset( $params['caller'] ) ) {
			$this->_caller = $params['caller'];
		}
		if ( isset( $params['footer'] ) ) {
			$this->_html_footer = $params['footer'];
		}
		if ( isset( $params['is_event'] ) ) {
			// Filter event post content, in single- and multi-post views
			add_filter( 'the_content', array( $this, 'event_content' ), PHP_INT_MAX - 1 );
			return;
		}
		// Replace page content - make sure it happens at (almost) the very end of
		add_filter( 'the_content', array( $this, 'append_content' ), PHP_INT_MAX - 1 );
	}

	/**
	 * Append locally generated content to normal page content. By default,
	 * first checks if we are in The Loop before outputting to prevent multiple
	 * calendar display - unless setting is turned on to skip this check.
	 * We should not append full calendar body to single event content as it
	 * leads to "calendar" nesting if default calendar page contains calendar
	 * shortcode.
	 *
	 * @param  string $content Post/Page content
	 * @return string          Modified Post/Page content
	 */
	public function append_content( $content ) {
		if (
			'calendar' === $this->_caller &&
			! $this->_registry->get( 'calendar.state' )->append_content()
		) {
			return $content;
		}
		$settings = $this->_registry->get( 'model.settings' );

		// Include any admin-provided page content in the placeholder specified in
		// the calendar theme template.
		if ( $settings->get( 'skip_in_the_loop_check' ) || in_the_loop() ) {
			$content = str_replace(
				self::CALENDAR_PLACEHOLDER,
				$content,
				$this->_html
			);
			$content .= $this->_html_footer;
		}
		return $content;
	}

	/**
	 * event_content function
	 *
	 * Filter event post content by inserting relevant details of the event
	 * alongside the regular post content.
	 *
	 * @param string $content Post/Page content
	 *
	 * @return string         Post/Page content
	 **/
	public function event_content( $content ) {
		if ( ! $this->_registry->get( 'calendar.state' )->append_content() ) {
			$content = '';
		}
		$to_return = $this->_html . $content;
		if ( isset( $this->_html_footer ) ) {
			$to_return .= $this->_html_footer;
		}
		// Pass the orginal content to the filter so that it can be modified
		return apply_filters(
			'ai1ec_event_content',
			$to_return,
			$content
		);
	}

}
