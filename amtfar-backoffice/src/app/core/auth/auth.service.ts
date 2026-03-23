import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  
  // En producción usar environment config
  private apiUrl = 'http://localhost:8080/api'; 
  
  private permisosSubject = new BehaviorSubject<string[]>([]);
  public permisos$ = this.permisosSubject.asObservable();

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
             this.permisosSubject.next(res.data.user.permisos);
          }
        }
      })
    );
  }

  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('permisos');
    this.permisosSubject.next([]);
    this.router.navigate(['/login']);
  }

  hasPermission(permiso: string): boolean {
    return this.permisosSubject.value.includes(permiso);
  }

  private loadTokenData() {
    const permStr = localStorage.getItem('permisos');
    if (permStr) {
      try {
        this.permisosSubject.next(JSON.parse(permStr));
      } catch (e) {
        this.permisosSubject.next([]);
      }
    }
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }
}
