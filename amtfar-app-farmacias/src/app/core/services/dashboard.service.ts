import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from '../auth/auth.service';

export interface ResumenCategoria {
  categoria: string;
  cantidad: number;
}

export interface PeriodoDashboard {
  id: string;
  mes: string;
  anio: number;
  estado: 'SIN_BOLETA' | 'IMPAGA' | 'PAGADA';
  monto?: number;
  boletaId?: number;
  bloqueado?: boolean;
}

export interface DashboardData {
  tieneDeuda: boolean;
  deudaTotal: number;
  periodos: PeriodoDashboard[];
  totalEmpleados: number;
  desgloseCategorias: ResumenCategoria[];
}

export interface DashboardResponse {
  status: string;
  data: DashboardData;
}

@Injectable({
  providedIn: 'root'
})
export class DashboardService {
  private http = inject(HttpClient);
  private authService = inject(AuthService);
  private apiUrl = 'http://amtfar-api.test/api/v1/farmacias/dashboard';

  private getHeaders() {
    const token = this.authService.getToken();
    return new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });
  }

  getDashboardData(): Observable<DashboardResponse> {
    return this.http.get<DashboardResponse>(this.apiUrl, { headers: this.getHeaders() });
  }
}
