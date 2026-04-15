<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse available rooms at SINEAD Hotel. Luxury accommodation in Berekuso — Standard, Deluxe, and Suite rooms available. Call to book your stay.">
    <title>Our Rooms | <?php echo APP_FULL_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/listing.css'); ?>">
</head>
<body>

    <!-- ═══════════════════════════════════════════════════════════════════════
         NAVBAR
    ════════════════════════════════════════════════════════════════════════ -->
    <nav class="listing-nav" id="listingNav">
        <div class="listing-nav-brand">
            <h1>SINEAD</h1>
            <span>Hotel &amp; Suites</span>
        </div>
        <div class="listing-nav-links">
            <a href="#rooms">Rooms</a>
            <a href="#contact">Contact</a>
            <a href="tel:<?php echo HOTEL_PHONE; ?>" class="btn-book-cta" style="border-color: var(--accent-gold);">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                Call to Book
            </a>
            <a href="<?php echo url('login'); ?>" class="btn-login-link">Staff Login</a>
        </div>
    </nav>


    <!-- ═══════════════════════════════════════════════════════════════════════
         HERO
    ════════════════════════════════════════════════════════════════════════ -->
    <section class="listing-hero">
        <div class="listing-hero-bg" style="background-image: url('<?php echo asset('images/hero-login.png'); ?>');"></div>
        <div class="listing-hero-overlay"></div>
        <div class="listing-hero-content">
            <h1>SINEAD</h1>
            <p class="hero-tagline">Where Luxury Meets Comfort</p>
            <p>Experience world-class hospitality in the heart of Berekuso. From our elegant Standard rooms to lavish Suites, every stay is crafted for distinction.</p>
            <a href="#rooms" class="btn-hero">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                View Our Rooms
            </a>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════════════
         ROOM LISTING
    ════════════════════════════════════════════════════════════════════════ -->
    <section class="listing-section" id="rooms">
        <div class="listing-section-header">
            <h2>Available Rooms</h2>
            <p>Choose from <?php echo $totalAvailable; ?> beautifully appointed rooms across three categories</p>
            <div class="accent-line"></div>
        </div>

        <!-- Type Filter Tabs -->
        <div class="type-tabs">
            <a href="<?php echo url('listing'); ?>#rooms" 
               class="type-tab <?php echo $typeFilter === '' ? 'active' : ''; ?>">
                All <span class="tab-count">(<?php echo $totalAvailable; ?>)</span>
            </a>
            <?php foreach (ROOM_TYPES as $rType): ?>
                <?php $count = $typeCounts[$rType] ?? 0; ?>
                <?php if ($count > 0): ?>
                <a href="<?php echo url('listing', ['type' => $rType]); ?>#rooms" 
                   class="type-tab <?php echo $typeFilter === $rType ? 'active' : ''; ?>">
                    <?php echo sanitize($rType); ?> <span class="tab-count">(<?php echo $count; ?>)</span>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Room Cards Grid -->
        <?php if (empty($rooms)): ?>
            <div style="text-align: center; padding: var(--space-3xl) 0; color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto var(--space-lg); display: block; opacity: 0.3;">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
                <h3 style="color: var(--text-secondary); margin-bottom: var(--space-sm);">No Rooms Available</h3>
                <p>All rooms are currently occupied. Please call us for upcoming availability.</p>
                <a href="tel:<?php echo HOTEL_PHONE; ?>" class="btn-hero" style="margin-top: var(--space-lg);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Call <?php echo HOTEL_PHONE; ?>
                </a>
            </div>
        <?php else: ?>
            <div class="listing-rooms-grid">
                <?php 
                    // Reuse the same image mapping from views/rooms/index.php
                    $imageMap = [
                        'Standard' => 'room-standard.png',
                        'Deluxe'   => 'room-deluxe.png',
                        'Suite'    => 'room-suite.png'
                    ];

                    // Amenities vary by type (for modals)
                    $amenitiesByType = [
                        'Standard' => ['Free Wi-Fi', 'Air Conditioning', 'LCD TV', 'Room Service', 'En-suite Bathroom', 'Daily Housekeeping'],
                        'Deluxe'   => ['Free Wi-Fi', 'Air Conditioning', 'Smart TV', 'Mini Bar', 'Premium Bedding', 'Room Service', 'Work Desk', 'En-suite Bathroom'],
                        'Suite'    => ['Free Wi-Fi', 'Air Conditioning', 'Smart TV', 'Full Mini Bar', 'Living Area', 'Premium Bedding', 'Butler Service', 'Jacuzzi Bath', 'Work Desk', 'Room Service'],
                    ];
                ?>
                <?php foreach ($rooms as $room): ?>
                    <?php $roomImage = $imageMap[$room['type']] ?? 'room-standard.png'; ?>
                    <div class="listing-room-card" 
                         onclick="openRoomModal(<?php echo htmlspecialchars(json_encode([
                             'room_number' => $room['room_number'],
                             'type'        => $room['type'],
                             'floor'       => $room['floor'],
                             'price'       => $room['price_per_night'],
                             'description' => $room['description'] ?? '',
                             'image'       => asset("images/{$roomImage}"),
                             'amenities'   => $amenitiesByType[$room['type']] ?? [],
                         ]), ENT_QUOTES, 'UTF-8'); ?>)">
                        <div class="listing-room-card-image-wrapper">
                            <img src="<?php echo asset("images/{$roomImage}"); ?>" 
                                 alt="<?php echo sanitize($room['type']); ?> Room" 
                                 class="listing-room-card-image"
                                 loading="lazy">
                            <div class="price-badge">
                                <?php echo formatCurrency($room['price_per_night']); ?> <span>/ night</span>
                            </div>
                        </div>
                        <div class="listing-room-card-body">
                            <div class="listing-room-card-type"><?php echo sanitize($room['type']); ?> Room</div>
                            <div class="listing-room-card-title">Room <?php echo sanitize($room['room_number']); ?></div>
                            <?php if (!empty($room['description'])): ?>
                                <div class="listing-room-card-desc"><?php echo sanitize($room['description']); ?></div>
                            <?php endif; ?>
                            <div class="listing-room-card-meta">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                                    Floor <?php echo (int)$room['floor']; ?>
                                </span>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Available
                                </span>
                            </div>
                        </div>
                        <div class="listing-room-card-footer">
                            <span class="text-muted text-sm"><?php echo sanitize($room['type']); ?> · Floor <?php echo (int)$room['floor']; ?></span>
                            <span class="btn-book-cta" onclick="event.stopPropagation(); window.location.href='tel:<?php echo HOTEL_PHONE; ?>'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                Book Now
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════════════
         CONTACT / CTA
    ════════════════════════════════════════════════════════════════════════ -->
    <section class="listing-cta-section" id="contact">
        <div class="listing-section" style="padding-top: var(--space-2xl); padding-bottom: var(--space-2xl);">
            <div class="listing-section-header">
                <h2>Book Your Stay</h2>
                <p>Contact our reservations team to secure your room</p>
                <div class="accent-line"></div>
            </div>
            <div class="listing-cta-grid">
                <div class="listing-cta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <h4>Call Us</h4>
                    <p><a href="tel:<?php echo HOTEL_PHONE; ?>"><?php echo HOTEL_PHONE; ?></a></p>
                </div>
                <div class="listing-cta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <h4>Email</h4>
                    <p><a href="mailto:<?php echo HOTEL_EMAIL; ?>"><?php echo HOTEL_EMAIL; ?></a></p>
                </div>
                <div class="listing-cta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <h4>Location</h4>
                    <p><?php echo HOTEL_ADDRESS; ?></p>
                </div>
                <div class="listing-cta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <h4>Hours</h4>
                    <p><?php echo HOTEL_HOURS; ?></p>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════════════════════════════════════ -->
    <footer class="listing-footer">
        &copy; <?php echo date('Y'); ?> <span>SINEAD</span> Hotel &amp; Suites. All rights reserved.
    </footer>


    <!-- ═══════════════════════════════════════════════════════════════════════
         ROOM DETAIL MODAL (reuses .modal-overlay / .modal from main.css)
    ════════════════════════════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="roomDetailModal">
        <div class="modal modal-lg">
            <img src="" alt="Room" class="modal-room-image" id="modalRoomImage">
            <div class="modal-body">
                <div class="modal-room-type-label" id="modalRoomType"></div>
                <h3 class="modal-room-title" id="modalRoomTitle"></h3>
                <div class="modal-room-price" id="modalRoomPrice"></div>
                <p class="text-muted" id="modalRoomDesc" style="margin-bottom: var(--space-lg); line-height: 1.7;"></p>
                <h4 style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: var(--space-md);">Room Amenities</h4>
                <div class="modal-room-amenities" id="modalRoomAmenities"></div>
                <a href="tel:<?php echo HOTEL_PHONE; ?>" class="btn-call-book">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    Call to Book — <?php echo HOTEL_PHONE; ?>
                </a>
            </div>
            <button class="modal-close" onclick="closeRoomDetailModal()" aria-label="Close" style="position: absolute; top: var(--space-md); right: var(--space-md); background: rgba(26,15,9,0.7); backdrop-filter: blur(4px); z-index: 5;">&times;</button>
        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════════════════════════
         SCRIPTS
    ════════════════════════════════════════════════════════════════════════ -->
    <script>
    // Navbar scroll effect
    (function() {
        var nav = document.getElementById('listingNav');
        if (nav) {
            window.addEventListener('scroll', function() {
                nav.classList.toggle('scrolled', window.scrollY > 40);
            });
        }
    })();

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Room detail modal
    function openRoomModal(room) {
        var modal = document.getElementById('roomDetailModal');
        document.getElementById('modalRoomImage').src = room.image;
        document.getElementById('modalRoomImage').alt = room.type + ' Room ' + room.room_number;
        document.getElementById('modalRoomType').textContent = room.type + ' Room';
        document.getElementById('modalRoomTitle').textContent = 'Room ' + room.room_number + ' · Floor ' + room.floor;
        document.getElementById('modalRoomPrice').innerHTML = 'KES ' + parseFloat(room.price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' <span>/ night</span>';
        document.getElementById('modalRoomDesc').textContent = room.description || 'A beautifully appointed ' + room.type.toLowerCase() + ' room with all modern amenities for a comfortable stay.';

        // Amenities
        var amenitiesEl = document.getElementById('modalRoomAmenities');
        amenitiesEl.innerHTML = '';
        (room.amenities || []).forEach(function(a) {
            amenitiesEl.innerHTML += '<div class="modal-room-amenity"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' + a + '</div>';
        });

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeRoomDetailModal() {
        var modal = document.getElementById('roomDetailModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close on overlay click
    document.getElementById('roomDetailModal').addEventListener('click', function(e) {
        if (e.target === this) closeRoomDetailModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRoomDetailModal();
    });
    </script>

</body>
</html>
