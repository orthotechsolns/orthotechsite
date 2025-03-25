/**
 * Main JavaScript for Ortho Photo Gallery
 */
(function($) {
    'use strict';
    
    // Initialize gallery functionality when document is ready
    $(document).ready(function() {
        initGalleryFilters();
    });
    
    /**
     * Initialize gallery category filters
     */
    function initGalleryFilters() {
        $('.opg-filters a').on('click', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const category = $this.data('category');
            
            // Update active class
            $('.opg-filters a').removeClass('active');
            $this.addClass('active');
            
            if (category === 'all') {
                // Show all items
                $('.opg-item').fadeIn(300);
            } else {
                // Hide all items first
                $('.opg-item').hide();
                // Show only items with matching category
                $('.opg-item.category-' + category).fadeIn(300);
            }
            
            // Optional: Use AJAX to fetch filtered items
            /*
            $.ajax({
                url: opg_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'opg_filter_gallery',
                    category: category,
                    nonce: opg_ajax.nonce
                },
                beforeSend: function() {
                    // Show loading indicator
                },
                success: function(response) {
                    if (response.success) {
                        // Update gallery with new items
                        updateGallery(response.data);
                    }
                },
                complete: function() {
                    // Hide loading indicator
                }
            });
            */
        });
    }
    
    /**
     * Update gallery with new items (for AJAX filtering)
     * 
     * @param {Array} items - Array of gallery items
     */
    function updateGallery(items) {
        const $gallery = $('.opg-gallery');
        
        // Clear current items
        $gallery.empty();
        
        if (items.length === 0) {
            $gallery.append('<p class="opg-no-items">No gallery items found.</p>');
            return;
        }
        
        // Add new items
        $.each(items, function(index, item) {
            let categoryNames = '';
            if (item.categories.length > 0) {
                categoryNames = item.categories.join(', ');
            }
            
            const $item = $(`
                <div class="opg-item">
                    <a href="${item.image_url}" data-lightbox="gallery" data-title="${item.title}">
                        <img src="${item.image_thumb}" alt="${item.title}">
                        <div class="opg-item-overlay">
                            <h3>${item.title}</h3>
                            ${item.caption ? '<p>' + item.caption + '</p>' : ''}
                            ${categoryNames ? '<span class="opg-categories">' + categoryNames + '</span>' : ''}
                        </div>
                    </a>
                </div>
            `);
            
            $gallery.append($item);
        });
    }
    
})(jQuery);