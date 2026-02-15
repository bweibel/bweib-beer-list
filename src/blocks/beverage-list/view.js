document.querySelectorAll( '.wp-block-beer-list-beverage-list' ).forEach( ( list ) => {
	const filterBar = list.querySelector( '.beverage-list__filters' );
	const searchInput = list.querySelector( '.beverage-list__search-input' );
	const paginationContainer = list.querySelector( '.beverage-list__pagination' );
	const grid = list.querySelector( '.beverage-list__grid' );
	const items = list.querySelectorAll( '.beverage-list__item' );

	// Capture original DOM order so sorting can always start from a stable baseline.
	const originalOrder = Array.from( items );

	let activeType = '';
	let searchTerm = '';
	let sortKey = '';
	let currentPage = 1;
	const perPage = paginationContainer
		? parseInt( paginationContainer.dataset.perPage, 10 ) || 6
		: 0;

	/**
	 * Return the numeric sort value for an item by field key.
	 * Items with no value (empty string) return null so they sort last
	 * regardless of sort direction.
	 */
	function getSortValue( item, field ) {
		const raw = item.dataset[ field ];
		if ( raw === undefined || raw === '' ) {
			return null;
		}
		return parseFloat( raw ) || 0;
	}

	/**
	 * Sort an array of items by the current sortKey.
	 * Items missing the sort field always appear at the end.
	 */
	function sortItems( matched ) {
		if ( ! sortKey ) {
			return matched;
		}

		const [ field, dir ] = sortKey.split( '-' ); // e.g. ['abv', 'asc']

		return [ ...matched ].sort( ( a, b ) => {
			const av = getSortValue( a, field );
			const bv = getSortValue( b, field );

			if ( av === null && bv === null ) return 0;
			if ( av === null ) return 1;
			if ( bv === null ) return -1;

			return dir === 'asc' ? av - bv : bv - av;
		} );
	}

	function getMatchedItems() {
		const term = searchTerm.toLowerCase();
		return originalOrder.filter( ( item ) => {
			const matchesType = ! activeType || item.dataset.types.split( ' ' ).includes( activeType );
			const matchesSearch = ! term || item.textContent.toLowerCase().includes( term );
			return matchesType && matchesSearch;
		} );
	}

	function createPageButton( label, onClick, { active = false, disabled = false } = {} ) {
		const btn = document.createElement( 'button' );
		btn.className = 'beverage-list__page-btn';
		btn.textContent = label;

		if ( active ) {
			btn.classList.add( 'is-active' );
		}
		if ( disabled ) {
			btn.classList.add( 'is-disabled' );
			btn.disabled = true;
		}

		btn.addEventListener( 'click', onClick );
		return btn;
	}

	function renderPagination( matchedCount ) {
		if ( ! paginationContainer ) {
			return;
		}

		paginationContainer.innerHTML = '';
		const totalPages = Math.ceil( matchedCount / perPage );

		if ( totalPages <= 1 ) {
			return;
		}

		const goToPage = ( page ) => {
			currentPage = page;
			applyAll();
		};

		paginationContainer.appendChild(
			createPageButton( '\u00AB', () => goToPage( currentPage - 1 ), { disabled: currentPage === 1 } )
		);

		for ( let i = 1; i <= totalPages; i++ ) {
			paginationContainer.appendChild(
				createPageButton( String( i ), () => goToPage( i ), { active: i === currentPage } )
			);
		}

		paginationContainer.appendChild(
			createPageButton( '\u00BB', () => goToPage( currentPage + 1 ), { disabled: currentPage === totalPages } )
		);
	}

	function applyAll() {
		const matched = getMatchedItems();
		const sorted = sortItems( matched );

		// Hide every item first.
		originalOrder.forEach( ( item ) => {
			item.hidden = true;
		} );

		// Determine the visible slice, then move and show each item in sorted
		// order by appending to the grid (appendChild moves existing nodes).
		let visible;
		if ( paginationContainer && perPage > 0 ) {
			const start = ( currentPage - 1 ) * perPage;
			visible = sorted.slice( start, start + perPage );
			renderPagination( sorted.length );
		} else {
			visible = sorted;
		}

		if ( grid ) {
			visible.forEach( ( item ) => {
				grid.appendChild( item );
				item.hidden = false;
			} );
		} else {
			visible.forEach( ( item ) => {
				item.hidden = false;
			} );
		}
	}

	if ( filterBar ) {
		filterBar.querySelectorAll( '.beverage-list__filter' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', () => {
				activeType = btn.dataset.type;
				currentPage = 1;

				filterBar.querySelectorAll( '.beverage-list__filter' ).forEach( ( b ) => b.classList.remove( 'is-active' ) );
				btn.classList.add( 'is-active' );

				applyAll();
			} );
		} );
	}

	if ( searchInput ) {
		searchInput.addEventListener( 'input', () => {
			searchTerm = searchInput.value;
			currentPage = 1;
			applyAll();
		} );
	}

	// Listen for external filter/sort events from the beverage-filter block.
	document.addEventListener( 'beverage-filter-change', ( e ) => {
		const detail = e.detail || {};

		if ( typeof detail.type === 'string' ) {
			activeType = detail.type;

			// Sync inline filter bar active state if present.
			if ( filterBar ) {
				filterBar.querySelectorAll( '.beverage-list__filter' ).forEach( ( b ) => {
					b.classList.toggle( 'is-active', b.dataset.type === activeType );
				} );
			}
		}

		if ( typeof detail.search === 'string' ) {
			searchTerm = detail.search;

			// Sync inline search input if present.
			if ( searchInput ) {
				searchInput.value = searchTerm;
			}
		}

		if ( typeof detail.sort === 'string' ) {
			sortKey = detail.sort;
		}

		currentPage = 1;
		applyAll();
	} );

	// Initial render — paginate on load if enabled.
	applyAll();
} );
