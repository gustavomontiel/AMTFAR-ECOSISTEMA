import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://amtfar-api.test/api/v1/auth';

  constructor(private http: HttpClient) {}

  login(usuario: string, clave: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, { username: usuario, password: clave });
  }
}
