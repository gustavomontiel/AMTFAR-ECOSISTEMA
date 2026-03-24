import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

export interface Periodo {
  mes: string;
  anio: number;
  estado: 'SIN_BOLETA' | 'IMPAGA';
  monto?: number;
  boletaId?: number;
  bloqueado?: boolean;
}

export interface ResumenCategoria {
  categoria: string;
  cantidad: number;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  isLoading = signal(true);
  
  // Estado General
  tieneDeuda = signal(true);
  deudaTotal = signal(45900);
  
  // Períodos 
  periodos = signal<Periodo[]>([]);
  
  // Resumen del Padrón (última DDJJ)
  totalEmpleados = signal(12);
  desgloseCategorias = signal<ResumenCategoria[]>([]);

  ngOnInit() {
    // Mocking API call for Dashboard initialization
    setTimeout(() => {
      this.periodos.set([
        { mes: 'Febrero', anio: 2026, estado: 'IMPAGA', monto: 45900, boletaId: 1052, bloqueado: false },
        { mes: 'Marzo', anio: 2026, estado: 'SIN_BOLETA', bloqueado: true } // Bloqueado cronológicamente por Febrero
      ]);
      
      this.desgloseCategorias.set([
        { categoria: 'Farmacéutico', cantidad: 3 },
        { categoria: 'Ayudante', cantidad: 4 },
        { categoria: 'Personal Auxiliar', cantidad: 5 }
      ]);
      
      this.isLoading.set(false);
    }, 800);
  }

  iniciarCarga(periodo: Periodo) {
    if (periodo.bloqueado) return;
    console.log('Navegando a carga de nómina para', periodo.mes);
    // Router navigate...
  }

  pagarBoleta(periodo: Periodo) {
    console.log('Abriendo pasarela o opciones para boleta', periodo.boletaId);
  }
}
