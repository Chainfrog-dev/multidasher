import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CreateRecipientComponent } from './create-recipient.component';

describe('CreateRecipientComponent', () => {
  let component: CreateRecipientComponent;
  let fixture: ComponentFixture<CreateRecipientComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CreateRecipientComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CreateRecipientComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
