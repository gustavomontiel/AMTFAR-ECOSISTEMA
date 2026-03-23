import { Component, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/auth/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.component.html',
})
export class LoginComponent {
  cuit = '';
  password = '';
  isLoading = false;
  hasError = false;
  showPassword = false;

  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);
  private authService = inject(AuthService);

  togglePasswordVisibility() {
    this.showPassword = !this.showPassword;
  }

  login() {
    if (!this.cuit || !this.password) return;
    
    this.isLoading = true;
    this.hasError = false;
    this.cdr.detectChanges();

    this.authService.login({ username: this.cuit, password: this.password, type: 'farmacia' }).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.router.navigate(['/app/dashboard']);
        }
      },
      error: (err) => {
        console.error('Login Failed', err);
        this.isLoading = false;
        this.hasError = true;
        this.cdr.detectChanges();
      }
    });
  }
}
