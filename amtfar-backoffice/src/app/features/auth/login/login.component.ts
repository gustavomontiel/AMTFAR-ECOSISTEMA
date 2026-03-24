import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/auth/auth.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html'
})
export class LoginComponent {
  usuario = signal('');
  password = signal('');
  isLoading = signal(false);

  constructor(private authService: AuthService, private router: Router) {}

  login() {
    if (!this.usuario() || !this.password()) {
      Swal.fire('Atención', 'Por favor complete todos los datos.', 'warning');
      return;
    }

    this.isLoading.set(true);
    
    this.authService.login(this.usuario(), this.password()).subscribe({
      next: (res) => {
        this.isLoading.set(false);
        if (res && res.data && res.data.token) {
          localStorage.setItem('amtfar_auth_token', res.data.token);
          this.router.navigate(['/app']);
        } else {
          Swal.fire('Error', 'No se recibió un token válido.', 'error');
        }
      },
      error: (err) => {
        this.isLoading.set(false);
        console.error('Error Login:', err);
        Swal.fire('Acceso Denegado', 'Credenciales incorrectas o usuario sin permisos.', 'error');
      }
    });
  }
}
