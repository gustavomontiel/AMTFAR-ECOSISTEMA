import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  private apiUrl = 'http://127.0.0.1:8888/api/v1/auth';

  private readonly TOKEN_KEY = 'amtfar_jwt';
  private readonly USER_KEY = 'amtfar_user';

  login(credentials: any) {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {
        if (response.status === 'success' && response.data.token) {
          localStorage.setItem(this.TOKEN_KEY, response.data.token);
          localStorage.setItem(this.USER_KEY, JSON.stringify(response.data.user));
        }
      })
    );
  }

  logout() {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  getUser(): any {
    const user = localStorage.getItem(this.USER_KEY);
    return user ? JSON.parse(user) : null;
  }

  isAuthenticated(): boolean {
    // Aquí en un ambiente real comprobaríamos la expiración del token también
    return !!this.getToken();
  }
}
