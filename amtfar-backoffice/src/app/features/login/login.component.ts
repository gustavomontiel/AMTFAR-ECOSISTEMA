import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  username = signal('');
  password = signal('');
  isLoading = signal(false);
  hasError = signal(false);

  private authService = inject(AuthService);
  private router = inject(Router);

  login() {
    if (!this.username() || !this.password()) return;

    this.isLoading.set(true);
    this.hasError.set(false);

    this.authService.login({ username: this.username(), password: this.password() }).subscribe({
      next: () => {
        this.router.navigate(['/dashboard']);
      },
      error: (err) => {
        console.error('Error de login', err);
        this.isLoading.set(false);
        this.hasError.set(true);
      }
    });
  }
}
