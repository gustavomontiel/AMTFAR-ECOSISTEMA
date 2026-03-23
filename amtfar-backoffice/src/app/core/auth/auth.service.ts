import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  
  // En producción usar environment config
  private apiUrl = 'http://127.0.0.1:8888/api/v1/auth'; 
  
  private permisosSignal = signal<string[]>([]);
  public permisos = this.permisosSignal.asReadonly();

  constructor() {
    this.loadTokenData();
  }

  login(credentials: {username: string, password: string}) {
    const payload = { ...credentials, type: 'backoffice' };
    return this.http.post<any>(`${this.apiUrl}/login`, payload).pipe(
      tap(res => {
        if (res.status === 'success') {
          localStorage.setItem('token', res.data.token);
          if (res.data.user.permisos) {
             localStorage.setItem('permisos', JSON.stringify(res.data.user.permisos));
             this.permisosSignal.set(res.data.user.permisos);
          }
        }
      })
    );
  }

  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('permisos');
    this.permisosSignal.set([]);
    this.router.navigate(['/login']);
  }

  hasPermission(permiso: string): boolean {
    return this.permisosSignal().includes(permiso);
  }

  private loadTokenData() {
    const permStr = localStorage.getItem('permisos');
    if (permStr) {
      try {
        this.permisosSignal.set(JSON.parse(permStr));
      } catch (e) {
        this.permisosSignal.set([]);
      }
    }
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }
}
