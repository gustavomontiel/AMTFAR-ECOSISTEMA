import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-reportes',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 mt-6">
      <h2 class="text-xl font-bold text-slate-800 mb-4">Reportes Confidenciales</h2>
      <p class="text-slate-600">Este panel es solo visible porque posees el permiso "ver_reportes".</p>
    </div>
  `
})
export class ReportesComponent {}
