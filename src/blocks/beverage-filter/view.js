document.querySelectorAll( '.wp-block-beer-list-beverage-filter' ).forEach( ( filter ) => {
	const filterBar = filter.querySelector( '.beverage-filter__buttons' );
	const searchInput = filter.querySelector( '.beverage-filter__search-input' );
	const sortBtns = filter.querySelectorAll( '.beverage-filter__btn--sort' );

	let activeType = '';
	let searchTerm = '';
	let sortKey = '';

	function dispatch() {
		document.dispatchEvent(
			new CustomEvent( 'beverage-filter-change', {
				detail: { type: activeType, search: searchTerm, sort: sortKey },
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

	sortBtns.forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			const field = btn.dataset.sort;
			const wasAsc  = btn.classList.contains( 'is-asc' );
			const wasDesc = btn.classList.contains( 'is-desc' );

			// Clear all sort buttons before applying the new state.
			sortBtns.forEach( ( b ) => b.classList.remove( 'is-asc', 'is-desc' ) );

			if ( ! wasAsc && ! wasDesc ) {
				// Inactive → ascending.
				btn.classList.add( 'is-asc' );
				sortKey = field + '-asc';
			} else if ( wasAsc ) {
				// Ascending → descending.
				btn.classList.add( 'is-desc' );
				sortKey = field + '-desc';
			} else {
				// Descending → clear.
				sortKey = '';
			}

			dispatch();
		} );
	} );
} );
