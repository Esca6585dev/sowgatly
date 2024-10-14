<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esca6585 - Web Developer Portfolio</title>
    <link rel="stylesheet" href="{{ asset('resume/style.css') }}">
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#projects">Projects</a></li>
                <li><a href="#skills">Skills</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="home">
            <h1>Esca6585</h1>
            <img class="circle-logo" src="{{ asset('img/logo/brand_logo.png') }}" alt="{{ asset('img/logo/brand_logo.png') }}">
            <h2>Web Developer & Designer</h2>
        </section>

        <section id="about">
            <h2>About Me</h2>
            <p>A passionate web developer with expertise in frontend and backend technologies. I create responsive and user-friendly web applications.</p>
        </section>

        <section id="projects">
            <h2>My Projects</h2>
            <div class="project-grid">
                <div class="project-card">
                    <h3>TDS.gov.tm</h3>
                    <p>Government website</p>
                    <a href="https://tds.gov.tm" target="_blank">View Project</a>
                </div>
                <div class="project-card">
                    <h3>Sowgatly.app</h3>
                    <p>Gift app</p>
                    <a href="https://sowgatly.app" target="_blank">View Project</a>
                </div>
                <div class="project-card">
                    <h3>Azyksowda.com.tm</h3>
                    <p>E-commerce platform</p>
                    <a href="https://azyksowda.com.tm" target="_blank">View Project</a>
                </div>
            </div>
        </section>

        <section id="skills">
            <h2>My Skills</h2>
            <ul class="skills-list">
                <li>HTML</li>
                <li>CSS</li>
                <li>JavaScript</li>
                <li>Laravel</li>
                <li>PHP</li>
                <li>Ajax</li>
                <li>jQuery</li>
                <li>React.js</li>
                <li>MUI</li>
                <li>Ant Design</li>
                <li>Flutter</li>
                <li>PWA</li>
                <li>Web Applications</li>
            </ul>
        </section>

        <section id="contact">
            <h2>Contact Me</h2>
            <form id="contact-form">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" placeholder="Your Message" required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Esca6585. All rights reserved.</p>
    </footer>

    <script src="{{ asset('resume/script.js') }}"></script>
</body>
</html>