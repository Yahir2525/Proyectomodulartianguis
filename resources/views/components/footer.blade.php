<footer class="footer">
  <div class="footer-container">

    <!-- Marca -->
    <div class="footer-brand text-black">
      <h4>Blancos Doña Colchas</h4>
      <p class="parrafo">© {{ date('Y') }} Todos los derechos reservados</p>
    </div>

    <!-- Enlaces rápidos -->
    <div class="footer-links">
      <h5>Enlaces</h5>
      <ul>
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Contacto</a></li>
        <li><a href="#">Políticas de privacidad</a></li>
      </ul>
    </div>

    <!-- Contacto -->
    <div class="footer-contact">
      <h5>Contacto</h5>
      <p class="parrafo"><i class="fa-solid fa-location-dot"></i> Calle Ejemplo #123, Ciudad</p>
      <p class="parrafo"><i class="fa-solid fa-phone"></i> +52 123 456 7890</p>
      <p class="parrafo"><i class="fa-solid fa-envelope"></i> contacto@misitio.com</p>
    </div>

    <!-- Redes sociales -->
    <div class="footer-social">
      <h5>Síguenos</h5>
      <a href="#"><i class="fa-brands fa-facebook"></i></a>
      <a href="#"><i class="fa-brands fa-twitter"></i></a>
      <a href="#"><i class="fa-brands fa-instagram"></i></a>
      <a href="#"><i class="fa-brands fa-tiktok"></i></a>
    </div>

  </div>
</footer>

<style>
/* ==== FOOTER ==== */
.footer {
  background: transparent;  /* puedes poner #f9f9f9 si lo quieres con color */
  padding: 30px 15px;
  margin-top: 40px;
  font-size: 0.9rem;
  width: 100%;

}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  text-align: center;     /* centra textos */
  justify-items: center;  /* centra los bloques en cada celda */
}

.footer-brand h4 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  justify-content: center;
}

/* Párrafos en negro fuerte */
.footer-brand p,
.footer-contact p,
.parrafo {
  margin: 4px 0;
  font-size: 0.9rem;
  color: #000;
}

.footer-links h5,
.footer-contact h5,
.footer-social h5 {
  font-size: 1rem;
  margin-bottom: 10px;
  font-weight: 600;
  color: #222;
}

.footer-links ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links ul li {
  margin: 6px 0;
}

.footer-links a {
  text-decoration: none;
  color: #222;
  transition: color 0.3s;
}

.footer-links a:hover {
  color: #000000ff;
}

.footer-contact i {
  margin-right: 6px;
  color: #000000ff;
}

.footer-social a {
  margin: 0 8px 0 0;
  font-size: 1.3rem;
  color: #000000ff;
  transition: color 0.3s;
}

.footer-social a:hover {
  color: #000000ff;
}

/* ==== Responsivo ==== */
@media (max-width: 640px) {
  .footer-container {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .footer-social a {
    margin: 0 5px;
  }
}
</style>
