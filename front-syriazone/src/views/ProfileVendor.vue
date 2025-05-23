<template>
  <div class="container">
    <SideBarvendor />
    <div class="dashboard">
      <div class="profile-container">
        <!-- Profile Header with Animation -->
        <div
          class="profile-header"
          :class="{ 'animate-header': animateHeader }"
        >
          <div class="avatar-container">
            <div
              class="avatar-wrapper"
              @mouseover="startAvatarAnimation"
              @mouseleave="stopAvatarAnimation"
            >
              <img
                :src="
                  profile.image ? profile.image : '/images/default-avatar.png'
                "
                :class="{ avatar: true, animated: avatarAnimating }"
                alt="Profile Image"
              />
              <label for="image-upload" class="upload-overlay">
                <span class="icon">📷</span>
                تغيير الصورة
              </label>
              <input
                id="image-upload"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                style="display: none"
              />
            </div>
          </div>
          <h1 class="profile-name">{{ profile.name }}</h1>
          <p class="profile-email">{{ profile.email }}</p>
        </div>

        <!-- Profile Info Card -->
        <div class="profile-card" :class="{ 'animate-card': animateCard }">
          <div class="card-header">
            <h2>معلومات الحساب</h2>
            <button
              class="edit-btn"
              @click="toggleEditMode"
              :class="{ active: editMode }"
            >
              {{ editMode ? 'حفظ التغييرات' : 'تعديل الملف الشخصي' }}
            </button>
          </div>

          <div class="card-body">
            <form @submit.prevent="updateProfile">
              <div class="form-group">
                <label>الاسم الكامل</label>
                <input
                  v-model="profile.name"
                  :readonly="!editMode"
                  :class="{ editable: editMode }"
                />
              </div>

              <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input
                  v-model="profile.email"
                  type="email"
                  :readonly="!editMode"
                  :class="{ editable: editMode }"
                />
              </div>

              <div class="form-group">
                <label>رقم الهاتف</label>
                <input
                  v-model="profile.phone"
                  type="tel"
                  :readonly="!editMode"
                  :class="{ editable: editMode }"
                  placeholder="أضف رقم هاتفك"
                />
              </div>

              <!-- Password Fields (Only show in edit mode) -->
              <transition name="slide-fade">
                <div v-if="editMode" class="password-fields">
                  <div class="form-group">
                    <label>كلمة المرور الجديدة</label>
                    <input
                      v-model="profile.password"
                      type="password"
                      placeholder="اتركه فارغاً إذا لم ترغب في التغيير"
                    />
                  </div>

                  <div class="form-group">
                    <label>تأكيد كلمة المرور</label>
                    <input
                      v-model="profile.password_confirmation"
                      type="password"
                      placeholder="تأكيد كلمة المرور الجديدة"
                    />
                  </div>
                </div>
              </transition>
            </form>
          </div>
        </div>

        <!-- Location Card -->
        <div class="location-card" :class="{ 'animate-card': animateCard }">
          <div class="card-header">
            <h2>موقعي على الخريطة</h2>
            <button v-if="editMode" class="location-btn" @click="openMapModal">
              تغيير الموقع
            </button>
          </div>

          <div class="map-container">
            <div ref="map" class="map"></div>
            <div class="coordinates">
              <span>خط العرض: {{ profile.location.lat }}</span>
              <span>خط الطول: {{ profile.location.lang }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Modal for Location Update -->
      <div
        v-if="showMapModal"
        class="modal-overlay"
        @click.self="closeMapModal"
      >
        <div class="modal-content">
          <button class="close-modal" @click="closeMapModal">✖</button>
          <h3>اختر موقعك الجديد</h3>

          <div class="modal-map-container">
            <div ref="modalMap" class="map"></div>
            <div class="modal-actions">
              <button class="cancel-btn" @click="closeMapModal">إلغاء</button>
              <button class="confirm-btn" @click="saveLocation">
                حفظ الموقع
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getData, postData } from '@/api'
import { useToast } from 'vue-toastification'
import SideBarvendor from '@/components/SideBarvendor.vue'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

export default {
  name: 'ProfilePage',
  components: { SideBarvendor },
  setup() {
    const toast = useToast()
    return { toast }
  },
  data() {
    return {
      profile: {
        id: null,
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        image: '',
        location: {
          lat: 0,
          lang: 0,
        },
      },
      editMode: false,
      animateHeader: false,
      animateCard: false,
      avatarAnimating: false,
      showMapModal: false,
      map: null,
      modalMap: null,
      marker: null,
      modalMarker: null,
      tempLocation: {
        lat: 0,
        lang: 0,
      },
    }
  },
  async created() {
    await this.fetchProfile()
  },
  mounted() {
    this.initAnimations()
  },
  methods: {
    async fetchProfile() {
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        const response = await getData('/vendor/profile/my_info', headers)
        this.profile = {
          ...response.data,
          password: '',
          password_confirmation: '',
        }

        // Initialize map after profile is loaded
        this.$nextTick(() => {
          this.initMap()
        })
      } catch (error) {
        this.toast.error('فشل في تحميل بيانات الملف الشخصي')
        console.error('Profile error:', error)
      }
    },

    initMap() {
      if (this.$refs.map) {
        this.map = L.map(this.$refs.map).setView(
          [this.profile.location.lat, this.profile.location.lang],
          15
        )

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(this.map)

        this.marker = L.marker(
          [this.profile.location.lat, this.profile.location.lang],
          { draggable: false }
        ).addTo(this.map)
      }
    },

    initModalMap() {
      if (this.$refs.modalMap) {
        this.modalMap = L.map(this.$refs.modalMap).setView(
          [this.profile.location.lat, this.profile.location.lang],
          15
        )

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(this.modalMap)

        this.modalMarker = L.marker(
          [this.profile.location.lat, this.profile.location.lang],
          { draggable: true }
        ).addTo(this.modalMap)

        this.modalMap.on('click', (e) => {
          this.tempLocation = {
            lat: e.latlng.lat,
            lang: e.latlng.lng,
          }
          this.modalMarker.setLatLng(e.latlng)
        })
      }
    },

    openMapModal() {
      this.tempLocation = { ...this.profile.location }
      this.showMapModal = true
      this.$nextTick(() => {
        this.initModalMap()
      })
    },

    closeMapModal() {
      this.showMapModal = false
      if (this.modalMap) {
        this.modalMap.remove()
        this.modalMap = null
      }
    },

    saveLocation() {
      this.profile.location = { ...this.tempLocation }
      this.closeMapModal()
    },

    toggleEditMode() {
      if (this.editMode) {
        this.updateProfile()
      } else {
        this.editMode = true
      }
    },

    async updateProfile() {
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        const formData = new FormData()

        // Prepare form data according to API requirements
        const fields = [
          { key: 'name', value: this.profile.name, type: 'text' },
          { key: 'phone', value: this.profile.phone || '', type: 'text' },
          { key: 'password', value: this.profile.password, type: 'text' },
          {
            key: 'password_confirmation',
            value: this.profile.password_confirmation,
            type: 'text',
          },
          {
            key: 'lat',
            value: this.profile.location.lat.toString(),
            type: 'text',
          },
          {
            key: 'lang',
            value: this.profile.location.lang.toString(),
            type: 'text',
          },
        ]

        // Add image if changed
        if (this.profile.image instanceof File) {
          formData.append('image', this.profile.image)
        }

        // Add other fields as JSON
        formData.append('data', JSON.stringify(fields))

        await postData('/vendor/profile/update', formData, headers)

        this.toast.success('تم تحديث الملف الشخصي بنجاح')
        this.editMode = false
        await this.fetchProfile()
      } catch (error) {
        this.toast.error('فشل في تحديث الملف الشخصي')
        console.error('Update error:', error)
      }
    },

    handleImageUpload(event) {
      const file = event.target.files[0]
      if (file) {
        // Create a preview of the new image
        const reader = new FileReader()
        reader.onload = (e) => {
          this.profile.image = e.target.result
        }
        reader.readAsDataURL(file)

        // Keep the file reference for upload
        this.profile.image = file
      }
    },

    startAvatarAnimation() {
      if (this.editMode) {
        this.avatarAnimating = true
      }
    },

    stopAvatarAnimation() {
      this.avatarAnimating = false
    },

    initAnimations() {
      setTimeout(() => {
        this.animateHeader = true
      }, 100)

      setTimeout(() => {
        this.animateCard = true
      }, 300)
    },
  },
}
</script>

<style scoped>
/* Base Styles */
.container {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard {
  padding: 2rem;
  background: #f8f9fa;
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Profile Header */
.profile-header {
  text-align: center;
  margin-bottom: 2rem;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease-out;
}

.profile-header.animate-header {
  opacity: 1;
  transform: translateY(0);
}

.avatar-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.avatar-wrapper {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.avatar-wrapper:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}

.avatar.animated {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.upload-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem;
  text-align: center;
  font-size: 0.9rem;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.avatar-wrapper:hover .upload-overlay {
  opacity: 1;
}

.profile-name {
  font-size: 2rem;
  color: #2c3e50;
  margin: 0.5rem 0;
}

.profile-email {
  color: #7f8c8d;
  font-size: 1.1rem;
  margin: 0;
}

/* Profile Cards */
.profile-card,
.location-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  padding: 1.5rem;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease-out;
}

.profile-card.animate-card,
.location-card.animate-card {
  opacity: 1;
  transform: translateY(0);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 1rem;
}

.card-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.edit-btn {
  background: #3498db;
  color: white;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 0.9rem;
}

.edit-btn:hover {
  background: #2980b9;
}

.edit-btn.active {
  background: #2ecc71;
}

/* Form Styles */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2c3e50;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:read-only {
  background: #f8f9fa;
  border-color: #eee;
  cursor: not-allowed;
}

.form-group input.editable {
  background: white;
  border-color: #3498db;
  cursor: text;
}

/* Password Fields Animation */
.slide-fade-enter-active {
  transition: all 0.3s ease;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter,
.slide-fade-leave-to {
  transform: translateX(10px);
  opacity: 0;
}

/* Location Card */
.location-btn {
  background: #9b59b6;
  color: white;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 0.9rem;
}

.location-btn:hover {
  background: #8e44ad;
}

.map-container {
  position: relative;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 1rem;
}

.map {
  width: 100%;
  height: 100%;
}

.coordinates {
  position: absolute;
  bottom: 10px;
  left: 10px;
  background: rgba(255, 255, 255, 0.9);
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.9rem;
  display: flex;
  gap: 1rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.close-modal {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7f8c8d;
}

.modal-map-container {
  height: 500px;
  margin-top: 1rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.cancel-btn {
  background: #95a5a6;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.confirm-btn {
  background: #2ecc71;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
  }

  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .profile-header {
    margin-top: 1rem;
  }

  .modal-content {
    width: 95%;
    padding: 1rem;
  }

  .modal-map-container {
    height: 400px;
  }
}
</style>
