( function( $ ) {

	'use strict';

	if ( typeof  kiwi === 'undefined' ||  kiwi === null ) {
		return;
	}

	 kiwi = $.extend( {
		cached: 0,
		inputs: []
	},  kiwi );

	$( function() {
		 kiwi.supportHtml5 = ( function() {
			var features = {};
			var input = document.createElement( 'input' );

			features.placeholder = 'placeholder' in input;

			var inputTypes = [ 'email', 'url', 'tel', 'number', 'range', 'date' ];

			$.each( inputTypes, function( index, value ) {
				input.setAttribute( 'type', value );
				features[ value ] = input.type !== 'text';
			} );

			return features;
		} )();

		$( 'div. kiwi > form' ).each( function() {
			var $form = $( this );
			 kiwi.initForm( $form );

			if (  kiwi.cached ) {
				 kiwi.refill( $form );
			}
		} );
	} );

	 kiwi.getId = function( form ) {
		return parseInt( $( 'input[name="_ kiwi"]', form ).val(), 10 );
	};

	 kiwi.initForm = function( form ) {
		var $form = $( form );

		$form.submit( function( event ) {
			if ( !  kiwi.supportHtml5.placeholder ) {
				$( '[placeholder].placeheld', $form ).each( function( i, n ) {
					$( n ).val( '' ).removeClass( 'placeheld' );
				} );
			}

			if ( typeof window.FormData === 'function' ) {
				 kiwi.submit( $form );
				event.preventDefault();
			}
		} );

		$( '. kiwi-submit', $form ).after( '<span class="ajax-loader"></span>' );

		 kiwi.toggleSubmit( $form );

		$form.on( 'click', '. kiwi-acceptance', function() {
			 kiwi.toggleSubmit( $form );
		} );

		// Exclusive Checkbox
		$( '. kiwi-exclusive-checkbox', $form ).on( 'click', 'input:checkbox', function() {
			var name = $( this ).attr( 'name' );
			$form.find( 'input:checkbox[name="' + name + '"]' ).not( this ).prop( 'checked', false );
		} );

		// Free Text Option for Checkboxes and Radio Buttons
		$( '. kiwi-list-item.has-free-text', $form ).each( function() {
			var $freetext = $( ':input. kiwi-free-text', this );
			var $wrap = $( this ).closest( '. kiwi-form-control' );

			if ( $( ':checkbox, :radio', this ).is( ':checked' ) ) {
				$freetext.prop( 'disabled', false );
			} else {
				$freetext.prop( 'disabled', true );
			}

			$wrap.on( 'change', ':checkbox, :radio', function() {
				var $cb = $( '.has-free-text', $wrap ).find( ':checkbox, :radio' );

				if ( $cb.is( ':checked' ) ) {
					$freetext.prop( 'disabled', false ).focus();
				} else {
					$freetext.prop( 'disabled', true );
				}
			} );
		} );

		// Placeholder Fallback
		if ( !  kiwi.supportHtml5.placeholder ) {
			$( '[placeholder]', $form ).each( function() {
				$( this ).val( $( this ).attr( 'placeholder' ) );
				$( this ).addClass( 'placeheld' );

				$( this ).focus( function() {
					if ( $( this ).hasClass( 'placeheld' ) ) {
						$( this ).val( '' ).removeClass( 'placeheld' );
					}
				} );

				$( this ).blur( function() {
					if ( '' === $( this ).val() ) {
						$( this ).val( $( this ).attr( 'placeholder' ) );
						$( this ).addClass( 'placeheld' );
					}
				} );
			} );
		}

		if (  kiwi.jqueryUi && !  kiwi.supportHtml5.date ) {
			$form.find( 'input. kiwi-date[type="date"]' ).each( function() {
				$( this ).datepicker( {
					dateFormat: 'yy-mm-dd',
					minDate: new Date( $( this ).attr( 'min' ) ),
					maxDate: new Date( $( this ).attr( 'max' ) )
				} );
			} );
		}

		if (  kiwi.jqueryUi && !  kiwi.supportHtml5.number ) {
			$form.find( 'input. kiwi-number[type="number"]' ).each( function() {
				$( this ).spinner( {
					min: $( this ).attr( 'min' ),
					max: $( this ).attr( 'max' ),
					step: $( this ).attr( 'step' )
				} );
			} );
		}

		// Character Count
		$( '. kiwi-character-count', $form ).each( function() {
			var $count = $( this );
			var name = $count.attr( 'data-target-name' );
			var down = $count.hasClass( 'down' );
			var starting = parseInt( $count.attr( 'data-starting-value' ), 10 );
			var maximum = parseInt( $count.attr( 'data-maximum-value' ), 10 );
			var minimum = parseInt( $count.attr( 'data-minimum-value' ), 10 );

			var updateCount = function( target ) {
				var $target = $( target );
				var length = $target.val().length;
				var count = down ? starting - length : length;
				$count.attr( 'data-current-value', count );
				$count.text( count );

				if ( maximum && maximum < length ) {
					$count.addClass( 'too-long' );
				} else {
					$count.removeClass( 'too-long' );
				}

				if ( minimum && length < minimum ) {
					$count.addClass( 'too-short' );
				} else {
					$count.removeClass( 'too-short' );
				}
			};

			$( ':input[name="' + name + '"]', $form ).each( function() {
				updateCount( this );

				$( this ).keyup( function() {
					updateCount( this );
				} );
			} );
		} );

		// URL Input Correction
		$form.on( 'change', '. kiwi-validates-as-url', function() {
			var val = $.trim( $( this ).val() );

			if ( val
			&& ! val.match( /^[a-z][a-z0-9.+-]*:/i )
			&& -1 !== val.indexOf( '.' ) ) {
				val = val.replace( /^\/+/, '' );
				val = 'http://' + val;
			}

			$( this ).val( val );
		} );
	};

	 kiwi.submit = function( form ) {
		if ( typeof window.FormData !== 'function' ) {
			return;
		}

		var $form = $( form );

		$( '.ajax-loader', $form ).addClass( 'is-active' );

		 kiwi.clearResponse( $form );

		var formData = new FormData( $form.get( 0 ) );

		var detail = {
			id: $form.closest( 'div. kiwi' ).attr( 'id' ),
			status: 'init',
			inputs: [],
			formData: formData
		};

		$.each( $form.serializeArray(), function( i, field ) {
			if ( '_ kiwi' == field.name ) {
				detail.contactFormId = field.value;
			} else if ( '_ kiwi_version' == field.name ) {
				detail.pluginVersion = field.value;
			} else if ( '_ kiwi_locale' == field.name ) {
				detail.contactFormLocale = field.value;
			} else if ( '_ kiwi_unit_tag' == field.name ) {
				detail.unitTag = field.value;
			} else if ( '_ kiwi_container_post' == field.name ) {
				detail.containerPostId = field.value;
			} else if ( field.name.match( /^_ kiwi_\w+_free_text_/ ) ) {
				var owner = field.name.replace( /^_ kiwi_\w+_free_text_/, '' );
				detail.inputs.push( {
					name: owner + '-free-text',
					value: field.value
				} );
			} else if ( field.name.match( /^_/ ) ) {
				// do nothing
			} else {
				detail.inputs.push( field );
			}
		} );

		 kiwi.triggerEvent( $form.closest( 'div. kiwi' ), 'beforesubmit', detail );

		var ajaxSuccess = function( data, status, xhr, $form ) {
			detail.id = $( data.into ).attr( 'id' );
			detail.status = data.status;
			detail.apiResponse = data;

			var $message = $( '. kiwi-response-output', $form );

			switch ( data.status ) {
				case 'validation_failed':
					$.each( data.invalidFields, function( i, n ) {
						$( n.into, $form ).each( function() {
							 kiwi.notValidTip( this, n.message );
							$( '. kiwi-form-control', this ).addClass( ' kiwi-not-valid' );
							$( '[aria-invalid]', this ).attr( 'aria-invalid', 'true' );
						} );
					} );

					$message.addClass( ' kiwi-validation-errors' );
					$form.addClass( 'invalid' );

					 kiwi.triggerEvent( data.into, 'invalid', detail );
					break;
				case 'acceptance_missing':
					$message.addClass( ' kiwi-acceptance-missing' );
					$form.addClass( 'unaccepted' );

					 kiwi.triggerEvent( data.into, 'unaccepted', detail );
					break;
				case 'spam':
					$message.addClass( ' kiwi-spam-blocked' );
					$form.addClass( 'spam' );

					 kiwi.triggerEvent( data.into, 'spam', detail );
					break;
				case 'aborted':
					$message.addClass( ' kiwi-aborted' );
					$form.addClass( 'aborted' );

					 kiwi.triggerEvent( data.into, 'aborted', detail );
					break;
				case 'mail_sent':
					$message.addClass( ' kiwi-mail-sent-ok' );
					$form.addClass( 'sent' );

					 kiwi.triggerEvent( data.into, 'mailsent', detail );
					break;
				case 'mail_failed':
					$message.addClass( ' kiwi-mail-sent-ng' );
					$form.addClass( 'failed' );

					 kiwi.triggerEvent( data.into, 'mailfailed', detail );
					break;
				default:
					var customStatusClass = 'custom-'
						+ data.status.replace( /[^0-9a-z]+/i, '-' );
					$message.addClass( ' kiwi-' + customStatusClass );
					$form.addClass( customStatusClass );
			}

			 kiwi.refill( $form, data );

			 kiwi.triggerEvent( data.into, 'submit', detail );

			if ( 'mail_sent' == data.status ) {
				$form.each( function() {
					this.reset();
				} );

				 kiwi.toggleSubmit( $form );
			}

			if ( !  kiwi.supportHtml5.placeholder ) {
				$form.find( '[placeholder].placeheld' ).each( function( i, n ) {
					$( n ).val( $( n ).attr( 'placeholder' ) );
				} );
			}

			$message.html( '' ).append( data.message ).slideDown( 'fast' );
			$message.attr( 'role', 'alert' );

			$( '.screen-reader-response', $form.closest( '. kiwi' ) ).each( function() {
				var $response = $( this );
				$response.html( '' ).attr( 'role', '' ).append( data.message );

				if ( data.invalidFields ) {
					var $invalids = $( '<ul></ul>' );

					$.each( data.invalidFields, function( i, n ) {
						if ( n.idref ) {
							var $li = $( '<li></li>' ).append( $( '<a></a>' ).attr( 'href', '#' + n.idref ).append( n.message ) );
						} else {
							var $li = $( '<li></li>' ).append( n.message );
						}

						$invalids.append( $li );
					} );

					$response.append( $invalids );
				}

				$response.attr( 'role', 'alert' ).focus();
			} );
		};

		$.ajax( {
			type: 'POST',
			url:  kiwi.apiSettings.getRoute(
				'/contact-forms/' +  kiwi.getId( $form ) + '/feedback' ),
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false
		} ).done( function( data, status, xhr ) {
			ajaxSuccess( data, status, xhr, $form );
			$( '.ajax-loader', $form ).removeClass( 'is-active' );
		} ).fail( function( xhr, status, error ) {
			var $e = $( '<div class="ajax-error"></div>' ).text( error.message );
			$form.after( $e );
		} );
	};

	 kiwi.triggerEvent = function( target, name, detail ) {
		var $target = $( target );

		/* DOM event */
		var event = new CustomEvent( ' kiwi' + name, {
			bubbles: true,
			detail: detail
		} );

		$target.get( 0 ).dispatchEvent( event );

		/* jQuery event */
		$target.trigger( ' kiwi:' + name, detail );
		$target.trigger( name + '. kiwi', detail ); // deprecated
	};

	 kiwi.toggleSubmit = function( form, state ) {
		var $form = $( form );
		var $submit = $( 'input:submit', $form );

		if ( typeof state !== 'undefined' ) {
			$submit.prop( 'disabled', ! state );
			return;
		}

		if ( $form.hasClass( ' kiwi-acceptance-as-validation' ) ) {
			return;
		}

		$submit.prop( 'disabled', false );

		$( '. kiwi-acceptance', $form ).each( function() {
			var $span = $( this );
			var $input = $( 'input:checkbox', $span );

			if ( ! $span.hasClass( 'optional' ) ) {
				if ( $span.hasClass( 'invert' ) && $input.is( ':checked' )
				|| ! $span.hasClass( 'invert' ) && ! $input.is( ':checked' ) ) {
					$submit.prop( 'disabled', true );
					return false;
				}
			}
		} );
	};

	 kiwi.notValidTip = function( target, message ) {
		var $target = $( target );
		$( '. kiwi-not-valid-tip', $target ).remove();
		$( '<span role="alert" class=" kiwi-not-valid-tip"></span>' )
			.text( message ).appendTo( $target );

		if ( $target.is( '.use-floating-validation-tip *' ) ) {
			var fadeOut = function( target ) {
				$( target ).not( ':hidden' ).animate( {
					opacity: 0
				}, 'fast', function() {
					$( this ).css( { 'z-index': -100 } );
				} );
			};

			$target.on( 'mouseover', '. kiwi-not-valid-tip', function() {
				fadeOut( this );
			} );

			$target.on( 'focus', ':input', function() {
				fadeOut( $( '. kiwi-not-valid-tip', $target ) );
			} );
		}
	};

	 kiwi.refill = function( form, data ) {
		var $form = $( form );

		var refillCaptcha = function( $form, items ) {
			$.each( items, function( i, n ) {
				$form.find( ':input[name="' + i + '"]' ).val( '' );
				$form.find( 'img. kiwi-captcha-' + i ).attr( 'src', n );
				var match = /([0-9]+)\.(png|gif|jpeg)$/.exec( n );
				$form.find( 'input:hidden[name="_ kiwi_captcha_challenge_' + i + '"]' ).attr( 'value', match[ 1 ] );
			} );
		};

		var refillQuiz = function( $form, items ) {
			$.each( items, function( i, n ) {
				$form.find( ':input[name="' + i + '"]' ).val( '' );
				$form.find( ':input[name="' + i + '"]' ).siblings( 'span. kiwi-quiz-label' ).text( n[ 0 ] );
				$form.find( 'input:hidden[name="_ kiwi_quiz_answer_' + i + '"]' ).attr( 'value', n[ 1 ] );
			} );
		};

		if ( typeof data === 'undefined' ) {
			$.ajax( {
				type: 'GET',
				url:  kiwi.apiSettings.getRoute(
					'/contact-forms/' +  kiwi.getId( $form ) + '/refill' ),
				beforeSend: function( xhr ) {
					var nonce = $form.find( ':input[name="_wpnonce"]' ).val();

					if ( nonce ) {
						xhr.setRequestHeader( 'X-WP-Nonce', nonce );
					}
				},
				dataType: 'json'
			} ).done( function( data, status, xhr ) {
				if ( data.captcha ) {
					refillCaptcha( $form, data.captcha );
				}

				if ( data.quiz ) {
					refillQuiz( $form, data.quiz );
				}
			} );

		} else {
			if ( data.captcha ) {
				refillCaptcha( $form, data.captcha );
			}

			if ( data.quiz ) {
				refillQuiz( $form, data.quiz );
			}
		}
	};

	 kiwi.clearResponse = function( form ) {
		var $form = $( form );
		$form.removeClass( 'invalid spam sent failed' );
		$form.siblings( '.screen-reader-response' ).html( '' ).attr( 'role', '' );

		$( '. kiwi-not-valid-tip', $form ).remove();
		$( '[aria-invalid]', $form ).attr( 'aria-invalid', 'false' );
		$( '. kiwi-form-control', $form ).removeClass( ' kiwi-not-valid' );

		$( '. kiwi-response-output', $form )
			.hide().empty().removeAttr( 'role' )
			.removeClass( ' kiwi-mail-sent-ok  kiwi-mail-sent-ng  kiwi-validation-errors  kiwi-spam-blocked' );
	};

	 kiwi.apiSettings.getRoute = function( path ) {
		var url =  kiwi.apiSettings.root;

		url = url.replace(
			 kiwi.apiSettings.namespace,
			 kiwi.apiSettings.namespace + path );

		return url;
	};

} )( jQuery );

/*
 * Polyfill for Internet Explorer
 * See https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent
 */
( function () {
	if ( typeof window.CustomEvent === "function" ) return false;

	function CustomEvent ( event, params ) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };
		var evt = document.createEvent( 'CustomEvent' );
		evt.initCustomEvent( event,
			params.bubbles, params.cancelable, params.detail );
		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
} )();
