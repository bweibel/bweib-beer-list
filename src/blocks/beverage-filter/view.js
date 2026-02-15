document.querySelectorAll( '.wp-block-beer-list-beverage-filter' ).forEach( ( filter ) => {
	const filterBar = filter.querySelector( '.beverage-filter__buttons' );
	const searchInput = filter.querySelector( '.beverage-filter__search-input' );

	let activeType = '';
	let searchTerm = '';

	function dispatch() {
		document.dispatchEvent(
			new CustomEvent( 'beverage-filter-change', {
				detail: { type: activeType, search: searchTerm },
			} )
		);
	}

	if ( filterBar ) {
		filterBar.querySelectorAll( '.beverage-filter__btn' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				activeType = btn.dataset.type;

				filterBar
					.querySelectorAll( '.beverage-filter__btn' )
					.forEach( ( b ) => b.classList.remove( 'is-active' ) );
				btn.classList.add( 'is-active' );

				dispatch();
			} );
		} );
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', () => {
			searchTerm = searchInput.value;
			dispatch();
		} );
	}
} );
