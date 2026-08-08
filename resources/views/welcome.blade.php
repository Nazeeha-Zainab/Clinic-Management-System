<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Care Plus Clinic | Your Health, Our Priority</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Landing CSS -->
    <link rel="stylesheet" href="/css/landing.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">
                <i class="fa-solid fa-heart-pulse"></i> Care Plus
            </a>
            <ul class="nav-links" style="margin: 0 auto;">
                <li><a href="#home" class="active">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#doctors">Doctors</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="#book" class="btn btn-primary" style="color: white;">Book Appointment</a>
                <a href="/login" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Your Health, <br><span>Our Priority</span></h1>
                <p>Experience world-class healthcare at Care Plus Clinic. We are dedicated to providing compassionate, high-quality medical services to you and your family in Kalmunai.</p>
                <div class="hero-cta">
                    <a href="#book" class="btn btn-primary">Book Appointment</a>
                    <a href="#about" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/doctor patient treatng.jpg') }}" alt="Healthcare Professionals">
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="container">
            <div class="section-title">
                <h2>About Care Plus Clinic</h2>
                <p>Committed to excellence in medical care</p>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
                <div>
                    <img src="{{ asset('images/Clinic center.avif') }}" alt="Hospital Building" style="width: 100%; border-radius: var(--radius); box-shadow: var(--shadow-md);">
                </div>
                <div>
                    <h3>Our Mission</h3>
                    <p>To improve the health and well-being of the communities we serve by providing accessible, high-quality, and compassionate healthcare services.</p>
                    
                    <h3 style="margin-top: 1.5rem;">Our Vision</h3>
                    <p>To be the leading healthcare provider in Kalmunai, recognized for our commitment to patient-centered care and medical excellence.</p>

                    <h3 style="margin-top: 1.5rem;">Why We Are Different</h3>
                    <ul style="list-style: none; color: var(--text-light); margin-top: 1rem;">
                        <li style="margin-bottom: 0.5rem;"><i class="fa-solid fa-check" style="color: var(--secondary-teal); margin-right: 10px;"></i> State-of-the-art medical facilities</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fa-solid fa-check" style="color: var(--secondary-teal); margin-right: 10px;"></i> Highly qualified medical professionals</li>
                        <li style="margin-bottom: 0.5rem;"><i class="fa-solid fa-check" style="color: var(--secondary-teal); margin-right: 10px;"></i> Comprehensive care under one roof</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="bg-gray-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Services</h2>
                <p>Comprehensive healthcare solutions for you and your family</p>
            </div>
            <div class="grid-3">
                <div class="card">
                    <div class="service-icon"><i class="fa-solid fa-stethoscope"></i></div>
                    <h3>General Consultation</h3>
                    <p>Expert medical advice and treatment for common illnesses, routine check-ups, and preventive care.</p>
                </div>
                <div class="card">
                    <div class="service-icon"><i class="fa-solid fa-baby"></i></div>
                    <h3>Pediatrics</h3>
                    <p>Specialized healthcare for infants, children, and adolescents ensuring their healthy growth.</p>
                </div>

                <div class="card">
                    <div class="service-icon"><i class="fa-solid fa-tooth"></i></div>
                    <h3>Dental Care</h3>
                    <p>Comprehensive dental services including checkups, cleaning, and advanced dental procedures.</p>
                </div>

                <div class="card">
                    <div class="service-icon"><i class="fa-solid fa-syringe"></i></div>
                    <h3>Vaccination</h3>
                    <p>Complete immunization programs for children and adults to protect against preventable diseases.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors">
        <div class="container">
            <div class="section-title">
                <h2>Meet Our Doctors</h2>
                <p>Dedicated professionals committed to your health</p>
            </div>
            <div class="grid-3">
                <div class="card doctor-card">
                    <img src="{{ asset('images/male doctor.avif') }}" alt="Dr. John Doe">
                    <div class="doctor-info">
                        <h3>Dr. Nazhath</h3>
                        <span class="doctor-spec">General Physician</span>
                        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">MBBS, MD - 10 Years Experience</p>
                        <p class="doctor-schedule"><i class="fa-regular fa-clock"></i> Mon - Sat: 9:00 AM - 9:00 PM</p>
                        <a href="#book" class="btn btn-secondary" style="width: 100%;">Book Consultation</a>
                    </div>
                </div>
                <div class="card doctor-card">
                    <img src="{{ asset('images/female doctor.jpg') }}" alt="Dr. Sarah Smith">
                    <div class="doctor-info">
                        <h3>Dr. Tharshana</h3>
                        <span class="doctor-spec">Pediatrician</span>
                        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">MBBS, DCH - 08 Years Experience</p>
                        <p class="doctor-schedule"><i class="fa-regular fa-clock"></i> Tue - Sun: 10:00 AM - 4:00 PM</p>
                        <a href="#book" class="btn btn-secondary" style="width: 100%;">Book Consultation</a>
                    </div>
                </div>
                <div class="card doctor-card">
                    <img src="{{ asset('images/male doctor 22.jpg') }}" alt="Dr. Michael Lee">
                    <div class="doctor-info">
                        <h3>Dr. Farook</h3>
                        <span class="doctor-spec">Dentist</span>
                        <p style="font-size: 0.9rem; margin-bottom: 0.5rem;">MBBS, MD, DM - 12 Years Experience</p>
                        <p class="doctor-schedule"><i class="fa-regular fa-clock"></i> Mon, Wed, Fri: 2:00 PM - 8:00 PM</p>
                        <a href="#book" class="btn btn-secondary" style="width: 100%;">Book Consultation</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Working Hours Section -->
    <section id="hours" class="bg-gray-section">
        <div class="container">
            <div class="section-title">
                <h2>Working Hours</h2>
                <p>We are here when you need us</p>
            </div>
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem; font-weight: 500;">Monday - Friday</td>
                        <td style="padding: 1rem; text-align: right; color: var(--text-light);">08:00 AM - 09:00 PM</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem; font-weight: 500;">Saturday & Sunday</td>
                        <td style="padding: 1rem; text-align: right; color: var(--text-light);">09:00 AM - 09:00 PM</td>
                    </tr>
                </table>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="why-us">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Us</h2>
                <p>What makes Care Plus Clinic your best choice</p>
            </div>
            <div class="grid-3">
                <div class="card" style="text-align: center;">
                    <i class="fa-solid fa-user-doctor" style="font-size: 2.5rem; color: var(--secondary-teal); margin-bottom: 1rem;"></i>
                    <h3>Experienced Doctors</h3>
                    <p>Highly qualified specialists dedicated to providing the best care.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fa-solid fa-laptop-medical" style="font-size: 2.5rem; color: var(--secondary-teal); margin-bottom: 1rem;"></i>
                    <h3>Online Booking</h3>
                    <p>Easy and hassle-free online appointment scheduling system.</p>
                </div>
                <div class="card" style="text-align: center;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 2.5rem; color: var(--secondary-teal); margin-bottom: 1rem;"></i>
                    <h3>Secure Records</h3>
                    <p>Your medical data is securely managed and kept strictly confidential.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="bg-gray-section">
        <div class="container">
            <div class="section-title">
                <h2>Patient Testimonials</h2>
                <p>What our patients say about us</p>
            </div>
            <div class="grid-2">
                <div class="card testimonial-card">
                    <i class="fa-solid fa-quote-left"></i>
                    <p class="testimonial-content">"The staff at Care Plus Clinic are incredibly friendly and professional. Dr. John took the time to explain everything clearly. Highly recommended!"</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Patient">
                        <div>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                            <h4 style="margin:0;">Amina M</h4>
                        </div>
                    </div>
                </div>
                <div class="card testimonial-card">
                    <i class="fa-solid fa-quote-left"></i>
                    <p class="testimonial-content">"Excellent facility and very short waiting time. The online booking system made it so easy to secure an appointment that fit my schedule."</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Patient">
                        <div>
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                            </div>
                            <h4 style="margin:0;">Rizwan H.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Book Appointment CTA -->
    <section id="book" style="background: var(--primary-blue); color: white; text-align: center;">
        <div class="container">
            <h2 style="color: white; margin-bottom: 1rem;">Ready to take care of your health?</h2>
            <p style="color: rgba(255,255,255,0.8); margin-bottom: 2rem; font-size: 1.125rem;">Schedule your consultation online and skip the waiting room.</p>
            <a href="/login" class="btn btn-teal" style="font-size: 1.2rem; padding: 1rem 2rem;">Book Your Appointment Now</a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch for any inquiries or assistance</p>
            </div>
            <div class="contact-grid">
                <div>
                    <h3>Clinic Location</h3>
                    <p style="margin-bottom: 2rem;">Visit us or contact us using the details below.</p>
                    
                    <div class="contact-info-card">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <h4 style="margin: 0 0 0.25rem;">Address</h4>
                            <p style="margin:0;">123 Main Street,<br>Kalmunai, Sri Lanka</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <h4 style="margin: 0 0 0.25rem;">Phone</h4>
                            <p style="margin:0;">+94 67 222 3344</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <h4 style="margin: 0 0 0.25rem;">Email</h4>
                            <p style="margin:0;">info@carepluskalmunai.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem;">Send a Message</h3>
                    <form id="contactForm">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;" class="form-group">
                            <input type="email" class="form-control" placeholder="Email Address" required>
                            <input type="tel" class="form-control" placeholder="Phone Number">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Subject" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h2 style="color: white; margin-bottom: 1rem;"><i class="fa-solid fa-heart-pulse" style="color: var(--secondary-teal);"></i> Care Plus</h2>
                    <p style="color: var(--text-light);">Your trusted healthcare partner in Kalmunai, providing compassionate care and medical excellence.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    </div>
                </div>
                <div>
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#doctors">Our Doctors</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Services</h3>
                    <ul class="footer-links">
                        <li><a href="#services">General Consultation</a></li>
                        <li><a href="#services">Pediatrics</a></li>
                        <li><a href="#services">Cardiology</a></li>
                        <li><a href="#services">Dental Care</a></li>
                        <li><a href="#services">Laboratory</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">Opening Hours</h3>
                    <ul class="footer-links" style="color: var(--text-light);">
                        <li style="display: flex; justify-content: space-between;"><span>Mon - Fri:</span> <span>08:00 AM - 09:00 PM</span></li>
                        <li style="display: flex; justify-content: space-between;"><span>Saturday:</span> <span>09:00 AM - 06:00 PM</span></li>
                        <li style="display: flex; justify-content: space-between;"><span>Sunday:</span> <span style="color: var(--danger);">Closed</span></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p style="margin: 0;">&copy; 2026 Care Plus Clinic. All Rights Reserved. | <a href="#" style="color: var(--secondary-teal); text-decoration: none;">Privacy Policy</a></p>
            </div>
        </div>
    </footer>

    <!-- Landing JS -->
    <script src="/js/landing.js"></script>
</body>
</html>
