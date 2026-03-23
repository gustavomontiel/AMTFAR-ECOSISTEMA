import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../core/auth/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  username = '';
  password = '';
  isLoading = false;
  hasError = false;

  private authService = inject(AuthService);

  login() {
    if (!this.username || !this.password) return;

    this.isLoading = true;
    this.hasError = false;

    this.authService.login({ username: this.username, password: this.password }).subscribe({
      next: () => {
        // Redirige manejado internamente si quisieramos, o aquí:
        // this.router.navigate(['/dashboard']);
      },
      error: (err) => {
        console.error('Error de login', err);
        this.isLoading = false;
        this.hasError = true;
      }
    });
  }
}
