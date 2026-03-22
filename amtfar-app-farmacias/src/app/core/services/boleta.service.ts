import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from '../auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class BoletaService {
  private http = inject(HttpClient);
  private authService = inject(AuthService);
  private apiUrl = 'http://127.0.0.1:8888/api/v1/boletas';

  private getHeaders() {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });
  }

  getUltimaBoleta(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/boletas/ultima`, { headers: this.getHeaders() });
  }

  getBoletas(): Observable<any> {
    return this.http.get(this.apiUrl, { headers: this.getHeaders() });
  }

  getBoleta(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/boletas/${id}`, { headers: this.getHeaders() });
  }

  calcularBoleta(payload: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/calcular`, payload, { headers: this.getHeaders() });
  }

  generarBoleta(payload: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, payload, { headers: this.getHeaders() });
  }

  getPersonaByCuil(cuil: string): Observable<any> {
    return this.http.get<any>(`http://127.0.0.1:8888/api/v1/personas/${cuil}`, { headers: this.getHeaders() });
  }

  getCategorias(): Observable<any> {
    return this.http.get<any>(`http://127.0.0.1:8888/api/v1/maestros/categorias`, { headers: this.getHeaders() });
  }
}
