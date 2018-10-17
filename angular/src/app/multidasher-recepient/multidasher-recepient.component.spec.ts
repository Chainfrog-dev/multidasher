import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherRecepientComponent } from './multidasher-recepient.component';

describe('MultidasherRecepientComponent', () => {
  let component: MultidasherRecepientComponent;
  let fixture: ComponentFixture<MultidasherRecepientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherRecepientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherRecepientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
