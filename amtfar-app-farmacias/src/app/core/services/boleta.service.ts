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
  private apiUrl = 'http://amtfar-api.test/api/v1/boletas';

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
    return this.http.get(`http://amtfar-api.test/api/v1/boletas/${id}`, { headers: this.getHeaders() });
  }

  descargarBoletaPdf(id: number): Observable<Blob> {
    return this.http.get(`http://amtfar-api.test/api/v1/boletas/${id}/pdf`, {
      headers: this.getHeaders(),
      responseType: 'blob'
    });
  }

  calcularBoleta(payload: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/calcular`, payload, { headers: this.getHeaders() });
  }

  generarBoleta(payload: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, payload, { headers: this.getHeaders() });
  }

  getPersonaByCuil(cuil: string): Observable<any> {
    return this.http.get<any>(`http://amtfar-api.test/api/v1/personas/${cuil}`, { headers: this.getHeaders() });
  }

  getCategorias(): Observable<any> {
    return this.http.get<any>(`http://amtfar-api.test/api/v1/maestros/categorias`, { headers: this.getHeaders() });
  }

  getEmpleadosActivos(): Observable<any> {
    return this.http.get<any>(`http://amtfar-api.test/api/v1/empleados`, { headers: this.getHeaders() });
  }
}
